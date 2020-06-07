<?php
/**
 * Requêtes permettant d'obtenir les téléphones recevant des SMS pour des groupes de parents
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Query
 * @filesource Telephones.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Query\Responsable;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Expression;
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
            'to' => new Expression('CONCAT_WS(" ", res.titre, res.nomSA, res.prenomSA)'),
            'telephoneF' => new Expression(
                'CASE WHEN smsF=1 THEN res.telephoneF ELSE NULL END'),
            'telephoneP' => new Expression(
                ' CASE WHEN smsP=1 THEN res.telephoneP ELSE NULL END'),
            'telephoneT' => new Expression(
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
        $on = new \Zend\Db\Sql\Expression(
            "IF(sco.demandeR2>0 AND sco.grilleTarifR2 = ?, res.responsableId=ele.responsable2Id,res.responsableId=ele.responsable1Id)",
            $keysgrille['grilleTarif']);
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
}