<?php
/**
 * Requêtes permettant d'obtenir les téléphones recevant des SMS pour des groupes de
 * parents
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Query
 * @filesource Telephones.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 juil. 2021
 * @version 2021-2.6.3
 */
namespace SbmCommun\Model\Db\Service\Query\Responsable;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Predicate;
use Zend\Db\Sql\Select;

class Telephones extends AbstractQuery
{
    use QueryTrait;

    protected function init()
    {
        $this->select = $this->sql->select()
            ->from(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ])
            ->columns($this->getColumnsResponsable())
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->having(
            [
                new Predicate\IsNotNull('TelephoneF'),
                new Predicate\IsNotNull('TelephoneP'),
                new Predicate\IsNotNull('TelephoneT')
            ], Predicate\PredicateSet::OP_OR);
    }

    private function getColumnsResponsable()
    {
        return [
            'responsableId' => 'responsableId',
            'to' => new Literal('CONCAT_WS(" ", res.titre, res.nomSA, res.prenomSA)'),
            'telephoneF' => new Literal(
                'CASE WHEN smsF=1 THEN res.telephoneF ELSE NULL END'),
            'telephoneP' => new Literal(
                ' CASE WHEN smsP=1 THEN res.telephoneP ELSE NULL END'),
            'telephoneT' => new Literal(
                ' CASE WHEN smsT=1 THEN res.telephoneT ELSE NULL END')
        ];
    }

    /**
     * Ne prend que les responsables qui ont des enfants scolarisés cette année scolaire,
     * non rayés et affectés sur au moins un circuit
     *
     * @param int $grilleTarif
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getResponsablesGrilleTarif($keysgrille)
    {
        return $this->renderResult($this->selectResponsablesGrilleTarif($keysgrille));
    }

    protected function selectResponsablesGrilleTarif($keysgrille)
    {
        $on = new \Zend\Db\Sql\Literal(
            sprintf(
                "IF(sco.demandeR2>0 AND sco.grilleTarifR2 = %d, res.responsableId=ele.responsable2Id,res.responsableId=ele.responsable1Id)",
                $keysgrille['grilleTarif']));
        $where = new Predicate\Predicate();
        $where->equalTo('sco.millesime', $this->millesime)
            ->literal('sco.inscrit = 1')
            ->nest()
            ->literal('sco.demandeR1 > 0')
            ->equalTo('sco.grilleTarifR1', $keysgrille['grilleTarif'])
            ->equalTo('sco.reductionR1', $keysgrille['reduit'])
            ->unnest()->or->nest()
            ->literal('sco.demandeR2 > 0')
            ->equalTo('sco.grilleTarifR2', $keysgrille['grilleTarif'])
            ->equalTo('sco.reductionR2', $keysgrille['reduit'])
            ->unnest();
        return $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([])
            ->from([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ])
            ->join([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ], 'sco.eleveId=ele.eleveId', [])
            ->join(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ], $on, $this->getColumnsResponsable())
            ->where($where);
    }

    public function getResponsableRelancer()
    {
        return $this->renderResult($this->selectResponsableRelancer());
    }

    protected function selectResponsableRelancer()
    {
        // élèves déjà réinscrits
        $where1 = new Predicate\Predicate();
        $where1->equalTo('millesime', $this->millesime);
        $subselectInscrits = $this->sql->select(
            $this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'eleveId'
        ])
            ->where($where1);
        // classes ayant une classe suivante
        $where2 = new Predicate\Predicate();
        $where2->isNotNull('suivantId');
        $subselectClasses = $this->sql->select(
            $this->db_manager->getCanonicName('classes', 'table'))
            ->columns([
            'classeId'
        ])
            ->where($where2);
        // responsables à relancer pour une réinscription
        $predicate = new Predicate\Predicate();
        $predicate->literal('sco.paiementR1 = 1')
            ->literal('sco.inscrit = 1')
            ->equalTo('sco.millesime', $this->millesime - 1)
            ->notIn('ele.eleveId', $subselectInscrits)
            ->in('classeId', $subselectClasses);
        $select = clone $this->select;
        return $select->join(
            [
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ], 'ele.responsable1Id = res.responsableId', [])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'sco.eleveId = ele.eleveId', [])
            ->where($predicate);
    }
}