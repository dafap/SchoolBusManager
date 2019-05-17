<?php
/**
 * Tous les calculs pour les paiements
 *
 * Service enregistré dans db_manager sous son nom
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Query/Paiement
 * @filesource Calculs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 mai 2019
 * @version 2019-4.5.0
 */
namespace SbmCommun\Model\Db\Service\Query\Paiement;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use SbmCommun\Model\Db\Sql\Predicate;
use SbmCommun\Model\Paiements\Resultats;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Literal;

class Calculs extends AbstractQuery
{

    private $resultats;

    protected function init()
    {
        $this->resultats = new Resultats();
    }

    /**
     * Renvoie les résultats d'analyse
     *
     * @param int $responsableId
     * @param array $arrayEleveId
     * @param bool $force
     *            force l'analyse, même si elle a déjà été faite
     * @return \SbmCommun\Model\Paiements\Resultats
     */
    public function getResultats(int $responsableId, array $arrayEleveId = null,
        bool $force = false): Resultats
    {
        if ($force || $this->resultats->isEmpty()) {
            $this->analyse($responsableId, $arrayEleveId);
        }
        return $this->resultats;
    }

    /**
     *
     * @param int $responsableId
     * @param array $arrayEleveid
     */
    private function analyse(int $responsableId, array $arrayEleveId = null)
    {
        $this->resultats->setResponsableId($responsableId);
        $this->resultats->setArrayEleveId($arrayEleveId);
        $this->analyseResponsable($responsableId);
        if (! is_null($arrayEleveId)) {
            $this->analyseListeEleves($arrayEleveId);
        }
    }

    /**
     *
     * @param int $responsableId
     */
    private function analyseResponsable(int $responsableId)
    {
        $this->calculAbonnementsResponsable($responsableId);
        $this->calculDuplicatasResponsables($responsableId);
        $this->calculPaiementsResponsable($responsableId);
    }

    /**
     *
     * @param array $arrayEleveId
     */
    private function analyseListeEleves(array $arrayEleveId)
    {
        if (empty($arrayEleveId)) {
            $arrayEleveId = [
                - 1
            ]; // pour compatibilité avec la clause IN
        }
        $this->calculAbonnementsListeEleves($arrayEleveId);
    }

    /**
     * Renvoie la relation de jointure entre les responsables et les élèves
     *
     * @param int $responsableId
     * @return \Zend\Db\Sql\Where
     */
    private function relationResponsableEleves(int $responsableId): Where
    {
        $where = new Where(null, Where::COMBINED_BY_OR);
        return $where->equalTo('responsable1Id', $responsableId)->equalTo(
            'responsable2Id', $responsableId);
    }

    /**
     *
     * @param int $responsableId
     */
    private function calculAbonnementsResponsable(int $responsableId)
    {
        $where = new Where(
            [
                $this->relationResponsableEleves($responsableId),
                new Literal('sco.selection = 0')
            ]);
        $this->appliquerGrilleTarif('inscrits',
            $this->selectAbonnementsElevesInscrits($where));
        $this->appliquerGrilleTarif('tous', $this->selectAbonnementsEleves($where));
    }

    /**
     *
     * @param array $arrayEleveId
     */
    private function calculAbonnementsListeEleves(array $arrayEleveId)
    {
        $where = new Where();
        $where->in('ele.eleveId', $arrayEleveId);
        $this->appliquerGrilleTarif('liste', $this->selectAbonnementsEleves($where));
        ;
    }

    /**
     *
     * @param string $nature
     *            'tous' ou 'inscrits' ou 'liste'
     * @param \Zend\Db\Sql\Select $select
     */
    private function appliquerGrilleTarif(string $nature, \Zend\Db\Sql\Select $select)
    {
        $effectifsParGrilleTarif = $this->renderResult($select);
        $tTarifs = $this->db_manager->get('Sbm\Db\Table\Tarifs');
        $detailAbonnements = [];
        $montantAbonnements = 0;
        foreach ($effectifsParGrilleTarif as $row) {
            $montantGrille = $tTarifs->getMontant($row['grilleCode'], $row['quantite']);
            $detailAbonnements[$row['grilleCode']] = [
                'grille' => $row['grilleTarif'],
                'quantite' => $row['quantite'],
                'montant' => $montantGrille
            ];
            $montantAbonnements += $montantGrille;
        }
        $this->resultats->setAbonnementsMontant($nature, $montantAbonnements);
        $this->resultats->setAbonnementsDetail($nature, $detailAbonnements);
        ;
    }

    /**
     *
     * @param int $responsableId
     */
    private function calculDuplicatasResponsables(int $responsableId)
    {
        $this->compterDuplicatas('tous',
            $this->selectDuplicatasParEleve(
                $this->relationResponsableEleves($responsableId)));
    }

    /**
     *
     * @param array $arrayEleveId
     */
    private function calculDuplicatasListeEleves(array $arrayEleveId)
    {
        $where = new Where();
        $where->in('ele.eleveId', $arrayEleveId);
        $this->compterDuplicatas('liste', $this->selectDuplicatasParEleve($where));
    }

    /**
     * Cette méthode met à jour le montant des duplicatas et la liste des élèves pour la
     * nature indiquée.
     *
     * @param string $nature
     *            'tous' ou 'liste'
     * @param \Zend\Db\Sql\Select $select
     */
    private function compterDuplicatas(string $nature, \Zend\Db\Sql\Select $select)
    {
        $duplicatasParEleve = $this->renderResult($select);
        $listeEleves = [];
        $nbDuplicatas = 0;
        foreach ($duplicatasParEleve as $row) {
            $nbDuplicatas += $row['duplicata'];
            $listeEleves[$row['eleveId']] = [
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'grilleCode' => $row['grilleCode'],
                'grilleTarif' => $row['grilleTarif'],
                'duplicata' => $row['duplicata'],
                'paiement' => $row['paiement']
            ];
        }
        $tTarifs = $this->db_manager->get('Sbm\Db\Table\Tarifs');
        $montantDuplicatas = $tTarifs->getMontant($tTarifs->getDuplicataCodeGrille(),
            $nbDuplicatas);
        $this->resultats->setListeEleves($nature, $listeEleves);
        $this->resultats->setMontantDuplicatas($nature, $montantDuplicatas);
    }

    /**
     *
     * @param int $responsableId
     */
    private function calculPaiementsResponsable(int $responsableId)
    {
        $as = sprintf('%d-%d', $this->millesime, $this->millesime + 1);
        $where = new Where();
        $where->equalTo('responsableId', $responsableId)->equalTo('anneeScolaire', $as);
        $tPaiements = $this->db_manager->get('Sbm\Db\Vue\Paiements');
        $this->resultats->setPaiementsTotal($tPaiements->total($where));
        $this->resultats->setPaiementsDetail(
            $tPaiements->fetchAll($where, 'datePaiement DESC')
                ->toArray());
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Db\Sql\Select
     */
    private function selectAbonnementsElevesInscrits(Where $where)
    {
        $predicates = [
            $where,
            new Literal('gratuit = 0')
        ];
        $elevesPayantsInscrits = new Predicate\ElevesPayantsInscrits($this->millesime,
            'sco', $predicates);
        return $this->selectAbonnements($elevesPayantsInscrits());
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Db\Sql\Select
     */
    private function selectAbonnementsEleves(Where $where)
    {
        $predicate = new Predicate\ElevesResponsablePayant($this->millesime, 'sco',
            [
                $where
            ]);
        return $this->selectAbonnements($predicate());
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Db\Sql\Select
     */
    private function selectAbonnements(Where $where)
    {
        $this->addStrategy('grilleTarif',
            $this->db_manager->get('Sbm\Db\Table\Tarifs')
                ->getStrategie('grille'));
        return $this->sql->select(
            [
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId = sco.eleveId',
            [
                'grilleCode' => 'grilleTarif',
                'grilleTarif' => 'grilleTarif'
            ])
            ->columns([
            'quantite' => new Literal('count(*)')
        ])
            ->where($where)
            ->group('grilleCode');
    }

    /**
     * Renvoie un Select permettant d'obtenir la liste des élèves d'un responsable ou d'un
     * tableau d'indentifiants (eleveId, nom, prénom, nb de duplicatas, grille tarifaire,
     * paiement enregistré)
     *
     * @param Where $where
     * @return \Zend\Db\Sql\Select
     */
    private function selectDuplicatasParEleve(Where $where)
    {
        $predicate = new Where([
            $where,
            new Literal('sco.selection = 0')
        ], Where::COMBINED_BY_AND);
        $predicate->equalTo('millesime', $this->millesime);
        $this->addStrategy('grilleTarif',
            $this->db_manager->get('Sbm\Db\Table\Tarifs')
                ->getStrategie('grille'));
        return $this->sql->select(
            [
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ])
            ->columns(
            [
                'eleveId' => 'eleveid',
                'nom' => 'nomSA',
                'prenom' => 'prenomSA'
            ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId = sco.eleveId',
            [
                'duplicata',
                'grilleCode' => 'grilleTarif',
                'grilleTarif',
                'paiement'
            ])
            ->where($predicate)
            ->order([
            'paiement DESC',
            'nomSA',
            'prenomSA'
        ]);
    }
}