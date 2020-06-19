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
 * @date 19 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Query\Responsable;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use SbmCommun\Model\Db\Sql\Predicate;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class Responsables extends AbstractQuery
{

    private function columnsResponsable()
    {
        return [
            'responsableId',
            'selection',
            'dateCreation',
            'dateModification',
            'nature',
            'titre',
            'nom',
            'nomSA',
            'prenom',
            'prenomSA',
            'titre2',
            'nom2',
            'nom2SA',
            'prenom2',
            'prenom2SA',
            'adresseL1',
            'adresseL2',
            'adresseL3',
            'codePostal',
            'communeId',
            'ancienAdresseL1',
            'ancienAdresseL2',
            'ancienAdresseL3',
            'ancienCodePostal',
            'ancienCommuneId',
            'email',
            'telephoneF',
            'telephoneP',
            'telephoneT',
            'smsF',
            'smsP',
            'smsT',
            'etiquette',
            'demenagement',
            'dateDemenagement',
            'facture', // inutilisé
            'grilleTarif', // inutilisé
            'ribTit', // inutilisé
            'ribDom', // inutilisé
            'iban', // inutilisé
            'bic', // inutilisé
            'x',
            'y',
            'userId',
            'id_tra', // inutilisé
            'note'
        ];
    }

    protected function init()
    {
        $this->select = $this->sql->select()
            ->from(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ])
            ->columns($this->columnsResponsable())
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'com.communeId=res.communeId',
            [
                'commune' => 'nom',
                'lacommune' => 'alias',
                'laposte' => 'alias_laposte'
            ]);
    }

    /**
     * Renvoie la liste des responsables avec le nombre d'élèves inscrits répondant au
     * where passé en paramètre, dans l'ordre demandé. ATTENTION ! L'élève est inscrit si
     * paiementR1 == 1 car c'est le R1 qui inscrit l'élève en payant. Le R2 ne compte pas
     * pour ça.
     *
     * @param Where $where
     * @param string $order
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function withNbElevesInscrits(Where $where, $order = null)
    {
        return $this->renderResult($this->selectNbElevesInscrits($where, $order));
    }

    protected function selectNbElevesInscrits(Where $where, $order = null)
    {
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiementR1 = 1')->OR->literal('fa = 1')->OR->literal('gratuit > 0')
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
        return $select;
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
    public function paginatorResponsables($where, $order = null, $responsableId = null)
    {
        // $select =$this->selectResponsables($where, $order, $responsableId);
        // die($this->getSqlString($select));
        return $this->paginator($this->unionResponsables($where, $order, $responsableId));
    }

    protected function unionResponsables($where, $order, $responsableId)
    {
        $select = new Select();
        $select->columns(
            array_merge($this->columnsResponsable(),
                [
                    'commune',
                    'lacommune',
                    'laposte',
                    'nbEnfants' => new Expression('count(eleveId)'),
                    'nbPreinscrits' => new Expression('count(preinscritId)'),
                    'nbInscrits' => new Expression('count(inscritId)'),
                    'nbGratuits' => new Expression('count(gratuitId)'),
                    'nbDuplicata' => new Expression('sum(duplicata)')
                ]))
            ->from([
            'tmp' => $this->unionSelect(1)
                ->combine($this->unionSelect(2))
        ])
            ->group('responsableId')
            ->order($order);
        return $where->count() ? $select->having($where) : $select;
    }

    /**
     *
     * @param int $r
     * @return \Zend\Db\Sql\Select
     */
    private function unionSelect(int $r): Select
    {
        $select = clone $this->select;
        $select->join([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ], sprintf('res.responsableId = ele.responsable%dId', $r), [
            'eleveId'
        ], $select::JOIN_LEFT)
            ->join([
            'pre' => $this->subselectPreinscrits()
        ], 'ele.eleveId=pre.eleveId', [
            'preinscritId' => 'eleveId'
        ], $select::JOIN_LEFT)
            ->join([
            'ins' => $this->subselectInscrits()
        ], 'ele.eleveId=ins.eleveId', [
            'inscritId' => 'eleveId'
        ], $select::JOIN_LEFT)
            ->join([
            'gra' => $this->subselectGratuits()
        ], 'ele.eleveId=gra.eleveId', [
            'gratuitId' => 'eleveId'
        ], $select::JOIN_LEFT)
            ->join([
            'dup' => $this->subselectDuplicatas($r)
        ], 'ele.eleveId=dup.eleveId', [
            'duplicata' => sprintf('duplicataR%d', $r)
        ], $select::JOIN_LEFT);
        if ($r == 2) {
            $select->where((new Where())->isNotNull('responsable2Id'));
        }
        return $select;
    }

    private function subselectPreinscrits()
    {
        $preinscrits = new Predicate\ElevesPreinscrits($this->millesime);
        $select = new Select();
        return $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'eleveId'
        ])
            ->where($preinscrits());
    }

    private function subselectInscrits()
    {
        $payants = new Predicate\ElevesPayantsInscrits($this->millesime);
        $select = new Select();
        return $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'eleveId'
        ])
            ->where($payants());
    }

    private function subselectGratuits()
    {
        $gratuits = new Predicate\ElevesGratuits($this->millesime);
        $select = new Select();
        return $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'eleveId'
        ])
            ->where($gratuits());
    }

    private function subselectDuplicatas(int $r)
    {
        $avecDuplicatas = new Predicate\ElevesAvecDuplicatas($this->millesime);
        $select = new Select();
        return $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'eleveId',
            'duplicataR' . $r
        ])
            ->where($avecDuplicatas($r));
    }

    /**
     * Renvoie un Select définissant la requête
     *
     * @param \Zend\Db\Sql\Where $where
     * @param array $order
     * @return \Zend\Db\Sql\Select
     */
    protected function selectResponsables($where, $order, $responsableId)
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
        /*
         * $enFA = new Predicate\ElevesEnFA($this->millesime); $select4 = new Select();
         * $select4->from($this->db_manager->getCanonicName('scolarites', 'table'))
         * ->columns([ 'eleveId' ]) ->where($enFA());
         */
        // avec duplicata
        $avecDuplicatas = new Predicate\ElevesAvecDuplicatas($this->millesime);
        $select5 = new Select();
        $select5->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'eleveId',
            'duplicataR1'
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
            /*->join([
            'fa' => $select4
        ], 'ele.eleveId=fa.eleveId', [
            'nbFa' => new Expression('count(fa.eleveId)')
        ], $select::JOIN_LEFT)*/
            ->join([
            'dup' => $select5
        ], 'ele.eleveId=dup.eleveId',
            [
                'nbDuplicata' => new Expression('sum(dup.duplicataR1)')
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
        return $this->renderResult($this->selectDejaInscritCetteAnnee($nomSA, $prenomSA))
            ->current()['nbEnfants'] > 0;
    }

    protected function selectDejaInscritCetteAnnee($nomSA, $prenomSA)
    {
        $where = new Where();
        $where->equalTo('res.nomSA', $nomSA)
            ->equalTo('res.prenomSA', $prenomSA)
            ->equalTo('sco.millesime', $this->millesime);
        return $this->sql->select(
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
    }
}