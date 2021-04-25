<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 *
 * @project sbm
 * @package SbmCommun/Arlysere/Tarification/Facture
 * @filesource Calculs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmCommun\Arlysere\Tarification\Facture;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use SbmCommun\Model\Db\Sql\Predicate;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Literal;

// use SbmBase\Model\StdLib;
class Calculs extends AbstractQuery
{

    // use \SbmCommun\Model\Traits\DebugTrait;

    /**
     *
     * @var Resultats
     */
    private $resultats;

    /**
     *
     * @var int
     */
    private $responsableId;

    /**
     *
     * @var bool
     */
    private $is_responsable;

    /**
     *
     * @var int[]
     */
    private $arrayEleveId;

    /**
     * Prix unitaire
     *
     * @var float
     */
    private $duplicataPU;

    /**
     * Méthode publique unique permettant de renvoyer un résultat.
     * Si un résultat a déjà
     * été préparé il est repris (sauf si force)
     *
     * @param int $responsableId
     * @param array $aEleveId
     * @param bool $force
     * @return Resultats
     */
    public function getResultats(int $responsableId, array $arrayEleveId = [],
        bool $force = false): Resultats
    {
        if ($this->responsableId != $responsableId || $force || $this->resultats->isEmpty()) {
            $this->responsableId = $responsableId;
            $this->is_responsable = $this->isResponsable();
            $this->arrayEleveId = $arrayEleveId ?: [
                - 1
            ]; // pour compatibilité avec la clause IN
            $this->resultats->setResponsableId($responsableId);
            $this->resultats->setArrayEleveId($arrayEleveId);
            $this->analyse();
        }
        return $this->resultats;
    }

    public function clearResultats()
    {
        $this->resultats->clear();
    }

    /**
     * Surcharge la méthode pour transmettre le millesime à l'objet Resultats
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Db\Service\Query\AbstractQuery::setMillesime()
     */
    public function setMillesime(int $millesime)
    {
        $this->millesime = $millesime;
        $this->resultats->setMillesime($millesime);
        return $this;
    }

    /**
     * Par défaut, le millesime est celui de la session
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Db\Service\Query\AbstractQuery::init()
     */
    protected function init()
    {
        $this->resultats = new Resultats($this->millesime);
        $this->addStrategy("grilleTarif",
            $this->db_manager->get('Sbm\Db\Table\Tarifs')
                ->getStrategie('grille'));
        $this->duplicataPU = 0;
        $this->responsableId = - 1;
    }

    /**
     *
     * @throws \SbmCommun\Arlysere\Exception\RuntimeException
     * @return number
     */
    private function getDuplicataPU()
    {
        if (! $this->duplicataPU) {
            // prix unitaire
            $duplicatas = $this->db_manager->get('Sbm\Db\Table\Tarifs')->fetchAll(
                [
                    'duplicata' => 1,
                    'millesime' => $this->millesime
                ]);
            if ($duplicatas->count() != 1) {
                throw new \SbmCommun\Arlysere\Exception\RuntimeException(
                    'La table des tarifs ne définit pas le montant d\'un duplicata pour l\'année en cours.');
            }
            $this->duplicataPU = $duplicatas->current()->montant;
            $this->resultats->setDuplicataPU($this->duplicataPU);
        }
        return $this->duplicataPU;
    }

    /**
     *
     * @param int $responsableId
     * @param array $arrayEleveId
     */
    private function analyse()
    {
        $this->analyseEnfants('tous');
        $this->analyseResponsable();
        if (! is_null($this->arrayEleveId)) {
            $this->analyseEnfants('liste');
            $this->analyseListeEleves();
        }
    }

    /**
     * Dresse la liste des enfants du responsable et calcule le montant des duplicatas
     *
     * @param string $nature
     *            'tous' ou 'liste'
     */
    private function analyseEnfants(string $nature)
    {
        for ($r = 1; $r <= 2; $r ++) {
            $arrayListe = [];
            $nbDuplicatas = 0;
            $where = new Where();
            $where->equalTo('millesime', $this->millesime);
            if ($nature == 'tous') {
                $where->equalTo('responsable' . $r . 'Id', $this->responsableId)
                    ->nest()
                    ->notEqualTo('gratuit', 1)->or->greaterThan("duplicataR$r", 0)
                    ->unnest()
                    ->literal('sco.selection = 0');
            } else {
                $where->in('ele.eleveId', $this->arrayEleveId);
            }
            foreach ($this->renderResult($this->selectEnfants($r, $where)) as $row) {
                $nbDuplicatas += $row['duplicata'];
                $arrayListe[$row['eleveId']] = $row->getArrayCopy();
            }
            $this->resultats->setListeEleves($r, $nature, $arrayListe);
            if ($nbDuplicatas) {
                $this->resultats->setNbDuplicatas($r, $nature, $nbDuplicatas);
                $this->resultats->setMontantDuplicatas($r, $nature,
                    $nbDuplicatas * $this->getDuplicataPU());
            } else {
                $this->resultats->setNbDuplicatas($r, $nature, 0);
                $this->resultats->setMontantDuplicatas($r, $nature, 0);
            }
        }
    }

    /**
     * Analyse les éléments de facture pour le responsableId de la classe Resultats
     */
    private function analyseResponsable()
    {
        $this->calculResponsableAbonnements();
        $this->calculResponsablePaiements();
    }

    /**
     * Analyde les éléments de facture pour la liste d'eleveId de la classe Resultats
     */
    private function analyseListeEleves()
    {
        // $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/tmp'),
        // 'analyse-liste.log');
        for ($r = 1; $r <= 2; $r ++) {
            $where = new Where();
            $where->in('ele.eleveId', $this->arrayEleveId)->equalTo(
                'responsable' . $r . 'Id', $this->responsableId);
            $select = $this->selectAbonnementsEleves($r, $where);
            // $this->debugLog($this->getSqlString($select));
            $this->getAbonnementsFratrie($r, 'liste', $select);
        }
    }

    /**
     * Lance le calcul du montant des abonnements et en dresse la liste, pour les inscrits
     * puis pour tous, en distinguant les enfants en R1 et ceux en R2
     */
    private function calculResponsableAbonnements(): void
    {
        for ($r = 1; $r <= 2; $r ++) {
            $where = new Where();
            $where->equalTo('responsable' . $r . 'Id', $this->responsableId);
            $this->getAbonnementsFratrie($r, 'inscrits',
                $this->selectAbonnementsElevesInscrits($r, $where));
            $this->getAbonnementsFratrie($r, 'tous',
                $this->selectAbonnementsEleves($r, $where));
        }
    }

    /**
     * Calcule le montant des abonnements et en dresse la liste dans le cas précisé par
     * les propriétés $r et $nature et enregistre les résultats dans la propriété
     * $resultats
     *
     * @param int $r
     *            1 pour R1, 2 pour R2
     * @param string $nature
     *            'tous' ou 'inscrits'
     * @param \Zend\Db\Sql\Select $select
     */
    private function getAbonnementsFratrie(int $r, string $nature,
        \Zend\Db\Sql\Select $select): void
    {
        $abonnementsFratrie = $this->db_manager->get('Sbm\AbonnementsFratrie');
        $abonnementsFratrie->resetEleves()->setDegressif($this->is_responsable);
        foreach ($this->renderResult($select) as $obj) {
            $abonnementsFratrie->addEleve($obj->getArrayCopy());
        }
        $this->resultats->setAbonnementsMontant($r, $nature, $abonnementsFratrie->total());
        $this->resultats->setAbonnementsDetail($r, $nature, $abonnementsFratrie->detail());
    }

    /**
     *
     * @param int $r
     *            1 ou 2 selon R1 ou R2
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Db\Sql\Select
     */
    private function selectAbonnementsElevesInscrits(int $r, Where $where): Select
    {
        $predicate = new Predicate\ElevesPayantsInscrits($this->millesime, 'sco',
            [
                $where
            ]);
        return $this->selectAbonnements($r, $predicate());
    }

    /**
     *
     * @param int $r
     *            1 ou 2 selon R1 ou R2
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Db\Sql\Select
     */
    private function selectAbonnementsEleves(int $r, Where $where): Select
    {
        $predicate = new Predicate\ElevesResponsablePayant($this->millesime, 'sco',
            [
                $where
            ]);
        return $this->selectAbonnements($r, $predicate());
    }

    /**
     *
     * @param int $r
     *            1 ou 2 selon R1 ou R2
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Db\Sql\Select
     */
    private function selectAbonnements(int $r, Where $where): Select
    {
        return $this->sql->select(
            [
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId = sco.eleveId',
            [
                'eleveId' => 'eleveId',
                'grilleTarif' => "grilleTarifR$r",
                'grille' => "grilleTarifR$r",
                'reduit' => "reductionR$r"
            ])
            ->columns([])
            ->where($where);
    }

    /**
     *
     * @param int $r
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Db\Sql\Select
     */
    private function selectEnfants(int $r, Where $where): Select
    {
        return $this->sql->select(
            [
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId = sco.eleveId',
            [
                'gratuit',
                'grilleTarif' => "grilleTarifR$r",
                'grilleCode' => "grilleTarifR$r",
                'reduction' => "reductionR$r",
                'duplicata' => "duplicataR$r",
                'paiement' => "paiementR$r"
            ])
            ->columns([
            'r' => new Literal("$r"),
            'eleveId',
            'nom',
            'prenom'
        ])
            ->where($where);
    }

    private function calculResponsablePaiements()
    {
        $as = sprintf('%d-%d', $this->millesime, $this->millesime + 1);
        $where = new Where();
        $where->equalTo('responsableId', $this->responsableId)->equalTo('anneeScolaire',
            $as);
        $tPaiements = $this->db_manager->get('Sbm\Db\Vue\Paiements');
        $this->resultats->setPaiementsMontant($tPaiements->total($where));
        $this->resultats->setPaiementsDetail(
            $tPaiements->fetchAll($where, 'datePaiement DESC')
                ->toArray());
    }

    /**
     * Indique si le responsableId de l'objet est un responsable.
     * Sinon cela peut être un
     * organisme, un gestionnaire, un administrateur, le sadmin ...
     *
     * @return bool
     */
    private function isResponsable(): bool
    {
        $select = $this->sql->select(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ])
            ->join([
            'usr' => $this->db_manager->getCanonicName('users', 'table')
        ], 'usr.email=res.email', [])
            ->columns([
            'isResponsable' => new Literal('count(*) = 0')
        ])
            ->where(
            (new Where())->literal('usr.categorieId <> 1')
                ->equalTo('res.responsableId', $this->responsableId));
        return $this->renderResult($select)->current()['isResponsable'] == 1;
    }
}