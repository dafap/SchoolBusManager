<?php
/**
 * Quelques requêtes utiles à partir de la table des responsables
 * (enregistré dans module.config.php sous 'Sbm\Db\Query\Responsables')
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query
 * @filesource Responsables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Query\Responsable;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use SbmCommun\Model\Db\Sql\Predicate;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class Responsables extends AbstractQuery
{

    protected function init()
    {
        $this->select = $this->sql->select()
            ->from(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ])
            ->columns(
            [
                'responsableId' => 'responsableId',
                'selection' => 'selection',
                'dateCreation' => 'dateCreation',
                'dateModification' => 'dateModification',
                'nature' => 'nature',
                'titre' => 'titre',
                'nom' => 'nom',
                'nomSA' => 'nomSA',
                'prenom' => 'prenom',
                'prenomSA' => 'prenomSA',
                'titre2' => 'titre2',
                'nom2' => 'nom2',
                'nom2SA' => 'nom2SA',
                'prenom2' => 'prenom2',
                'prenom2SA' => 'prenom2SA',
                'adresseL1' => 'adresseL1',
                'adresseL2' => 'adresseL2',
                'codePostal' => 'codePostal',
                'communeId' => 'communeId',
                'ancienAdresseL1' => 'ancienAdresseL1',
                'ancienAdresseL2' => 'ancienAdresseL2',
                'ancienCodePostal' => 'ancienCodePostal',
                'ancienCommuneId' => 'ancienCommuneId',
                'email' => 'email',
                'telephoneF' => 'telephoneF',
                'telephoneP' => 'telephoneP',
                'telephoneT' => 'telephoneT',
                'smsF' => 'smsF',
                'smsP' => 'smsP',
                'smsT' => 'smsT',
                'etiquette' => 'etiquette',
                'demenagement' => 'demenagement',
                'dateDemenagement' => 'dateDemenagement',
                'facture' => 'facture', // inutilisé
                'grilleTarif' => 'grilleTarif', // inutilisé
                'ribTit' => 'ribTit', // inutilisé
                'ribDom' => 'ribDom', // inutilisé
                'iban' => 'iban', // inutilisé
                'bic' => 'bic', // inutilisé
                'x' => 'x',
                'y' => 'y',
                'userId' => 'userId',
                'id_mgc' => 'id_mgc', // inutilisé
                'note' => 'note'
            ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'com.communeId=res.communeId', [
            'commune' => 'nom'
        ]);
    }

    /**
     * Renvoie la liste des responsables avec le nombre d'élèves inscrits répondant au
     * where passé en paramètre, dans l'ordre demandé
     *
     * @param Where $where
     * @param string $order
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function withNbElevesInscrits(Where $where, $order = null)
    {
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiement = 1')->OR->literal('fa = 1')->OR->literal('gratuit > 0')
            ->unnest()
            ->equalTo('millesime', $this->millesime);
        $select = clone $this->select;
        $select->join([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ],
            'res.responsableId = ele.responsable1Id Or res.responsableId = ele.responsable2Id',
            [
                'nb' => new Expression('count(ele.eleveId)')
            ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId=sco.eleveId', [])
            ->where($where);
        if (! is_null($order)) {
            $select->order($order);
        }
        return $this->renderResult($select);
    }

    public function getNbEnfantsInscrits($responsableId)
    {
        $where = new Where();
        $where->equalTo('responsableId', $responsableId);
        $result = $this->withNbElevesInscrits($where);
        return $result->current()['nb'];
    }

    public function hasEnfantInscrit($responsableId)
    {
        return $this->getNbEnfantsInscrits($responsableId) > 0;
    }

    /**
     * Renvoie le résultat d'une requête avec nombre d'enfants, d'inscrits et de
     * préinscrits
     *
     * @param \Zend\Db\Sql\Where $where
     * @param array $order
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function withEffectifs($where, $order = null, $responsableId = null)
    {
        return $this->renderResult(
            $this->selectResponsables($where, $order, $responsableId));
    }

    /**
     * Renvoie un paginator sur la requête donnant les responsables avec la commune et le
     * nombre d'enfants connus, inscrits et préinscrits
     *
     * @param \Zend\Db\Sql\Where $where
     * @param array $order
     * @return \Zend\Paginator\Paginator
     */
    public function paginator($where, $order = null, $responsableId = null)
    {
        return parent::paginator(
            $this->selectResponsables($where, $order, $responsableId));
    }

    /**
     * Renvoie un Select définissant la requête
     *
     * @param \Zend\Db\Sql\Where $where
     * @param array $order
     * @return \Zend\Db\Sql\Select
     */
    private function selectResponsables($where, $order, $responsableId)
    {
        // préinscrits
        $preinscrits = new Predicate\ElevesPreinscrits($this->millesime);
        $select1 = new Select();
        $select1->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'eleveId'
        ])
            ->where($preinscrits());
        // payants inscrits (pas fa et direct ou par un organisme)
        $payants = new Predicate\ElevesPayantsInscrits($this->millesime);
        $select2 = new Select();
        $select2->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'eleveId'
        ])
            ->where($payants());
        // gratuits
        $gratuits = new Predicate\ElevesGratuits($this->millesime);
        $select3 = new Select();
        $select3->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'eleveId'
        ])
            ->where($gratuits());
        // en famille d'accueil
        $enFA = new Predicate\ElevesEnFA($this->millesime);
        $select4 = new Select();
        $select4->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'eleveId'
        ])
            ->where($enFA());
        // avec duplicata
        $avecDuplicatas = new Predicate\ElevesAvecDuplicatas($this->millesime);
        $select5 = new Select();
        $select5->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'eleveId',
            'duplicata'
        ])
            ->where($avecDuplicatas());

        // ================= requête principale =====================
        $select = clone $this->select;
        $select->join([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ],
            'res.responsableId = ele.responsable1Id Or res.responsableId = ele.responsable2Id',
            [
                'nbEnfants' => new Expression('count(ele.eleveId)')
            ], $select::JOIN_LEFT)
            ->join([
            'pre' => $select1
        ], 'ele.eleveId=pre.eleveId',
            [
                'nbPreinscrits' => new Expression('count(pre.eleveId)')
            ], $select::JOIN_LEFT)
            ->join([
            'ins' => $select2
        ], 'ele.eleveId=ins.eleveId',
            [
                'nbInscrits' => new Expression('count(ins.eleveId)')
            ], $select::JOIN_LEFT)
            ->join([
            'gra' => $select3
        ], 'ele.eleveId=gra.eleveId',
            [
                'nbGratuits' => new Expression('count(gra.eleveId)')
            ], $select::JOIN_LEFT)
            ->join([
            'fa' => $select4
        ], 'ele.eleveId=fa.eleveId', [
            'nbFa' => new Expression('count(fa.eleveId)')
        ], $select::JOIN_LEFT)
            ->join([
            'dup' => $select5
        ], 'ele.eleveId=dup.eleveId',
            [
                'nbDuplicata' => new Expression('sum(dup.duplicata)')
            ], $select::JOIN_LEFT)
            ->group('responsableId')
            ->order($order);
        return $where->count() ? $select->having($where) : $select;
    }

    /**
     * Renvoie vrai si le responsable existe et s'il a des enfants inscrits dans ce
     * millesime
     *
     * @param string $nomSA
     * @param string $prenomSA
     */
    public function estDejaInscritCetteAnnee($nomSA, $prenomSA)
    {
        $where = new Where();
        $where->equalTo('res.nomSA', $nomSA)
            ->equalTo('res.prenomSA', $prenomSA)
            ->equalTo('sco.millesime', $this->millesime);
        $select = $this->sql->select(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ])
            ->join([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ],
            'res.responsableId = ele.responsable1Id Or res.responsableId = ele.responsable2Id',
            [])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'sco.eleveId = ele.eleveId', [])
            ->columns([
            'nbEnfants' => new Expression('count(sco.eleveId)')
        ])
            ->where($where);
        return $this->renderResult($select)->current()['nbEnfants'] > 0;
    }
}