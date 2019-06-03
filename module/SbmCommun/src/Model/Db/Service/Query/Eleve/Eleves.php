<?php
/**
 * Requête permettant d'obtenir des détails sur les élèves
 *
 * La table principale est `eleves`. Les tables jointes le sont par des LEFT JOIN ce qui rend les jointures non exclusives.
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Eleve
 * @filesource Eleves.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class Eleves extends AbstractQuery
{

    protected function init()
    {
        $this->addStrategy('grilleTarif',
            $this->db_manager->get('Sbm\Db\Table\Tarifs')
            ->getStrategie('grille'));
    }

    private function dernierMillesime($lequel, $responsableId)
    {
        $predicate = new Where();
        $predicate->literal('sc2.eleveId=sco.eleveId');
        $select2 = new Select();
        $select2->from(
            [
                'sc2' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns([
            'dernierMillesime' => new Literal('max(millesime)')
        ])
            ->where($predicate);
        $where = new Where();
        $where->equalTo('res.responsableId', $responsableId)
            ->nest()
            ->isNull('millesime')->or->equalTo('millesime', $select2)->unnest();
        $select = $this->sql->select()
            ->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->columns(
            [
                'eleveId' => 'eleveId',
                'mailchimp' => 'mailchimp',
                'dateCreation' => 'dateCreation',
                'dateModificationEleve' => 'dateModification',
                'nom' => 'nom',
                'nomSA' => 'nomSA',
                'prenom' => 'prenom',
                'prenomSA' => 'prenomSA',
                'dateN' => 'dateN',
                'sexe' => 'sexe',
                'numero' => 'numero',
                'responsable1Id' => 'responsable1Id',
                'x1' => 'x1',
                'y1' => 'y1',
                'responsable2Id' => 'responsable2Id',
                'x2' => 'x2',
                'y2' => 'y2',
                'responsableFId' => 'responsableFId',
                'selectionEleve' => 'selection',
                'noteEleve' => 'note'
            ])
            ->join(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'res.responsableId = ele.' . $lequel, [])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId = sco.eleveId',
            [
                'millesime',
                'regimeId',
                'paiement',
                'inscrit',
                'fa',
                'gratuit',
                'demandeR1',
                'demandeR2',
                'accordR1',
                'accordR2',
                'subventionR1',
                'subventionR2',
                'grilleTarif'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'sco.etablissementId = eta.etablissementId', [
                'etablissement' => 'nom'
            ], Select::JOIN_LEFT)
            ->join([
            'cometa' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'eta.communeId = cometa.communeId', [
            'communeEtablissement' => 'nom'
        ], Select::JOIN_LEFT)
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'cla.classeId = sco.classeId', [
            'classe' => 'nom'
        ], Select::JOIN_LEFT)
            ->join(
            [
                'photos' => $this->db_manager->getCanonicName('elevesphotos', 'table')
            ], 'photos.eleveId = ele.eleveId',
            [
                'sansphoto' => new Expression(
                    'CASE WHEN isnull(photos.eleveId) THEN TRUE ELSE FALSE END')
            ], Select::JOIN_LEFT)
            ->where($where);
            echo sprintf("<pre>%s</pre>\n",$this->getSqlString($select));
        return $this->renderResult($select);
    }

    public function duResponsable1($responsableId)
    {
        return $this->dernierMillesime('responsable1Id', $responsableId);
    }

    public function duResponsable2($responsableId)
    {
        return $this->dernierMillesime('responsable2Id', $responsableId);
    }

    public function duResponsableFinancier($responsableId)
    {
        return $this->dernierMillesime('responsableFId', $responsableId);
    }
}
