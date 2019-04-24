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
 * @date 24 avr. 2019
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
     * @param mixed $param
     * @param bool $force
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
    private function analyse($responsableId, $arrayEleveId)
    {
        $this->resultats->setResponsableId($responsableId);
        $this->resultats->setArrayEleveId($arrayEleveId);
        $this->analyseResponsable($responsableId);
        if (! is_null($arrayEleveId)) {
            $this->analyseListeEleves($arrayEleveId);
        }
    }

    private function analyseResponsable(int $responsableId)
    {
        $this->calculAbonnementsResponsable($responsableId);
        $this->calculDuplicatasResponsables($responsableId);
        $this->calculPaiementsResponsable($responsableId);
    }

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

    private function calculAbonnementsResponsable($responsableId)
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

    private function calculAbonnementsListeEleves(array $arrayEleveId)
    {
        $where = new Where();
        $where->in('ele.eleveId', $arrayEleveId);
        $this->appliquerGrilleTarif('liste', $this->selectAbonnementsEleves($where));
        ;
    }

    private function appliquerGrilleTarif($nature, $select)
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
        $this->resultats->setAbonnements($nature,
            [
                'detailAbonnements' => $detailAbonnements,
                'montantAbonnements' => $montantAbonnements
            ]);
    }

    private function calculDuplicatasResponsables($responsableId)
    {
        $this->compterDuplicatas('tous',
            $this->selectDuplicatasParEleve(
                $this->relationResponsableEleves($responsableId)));
    }

    private function calculDuplicatasListeEleves(array $arrayEleveId)
    {
        $where = new Where();
        $where->in('ele.eleveId', $arrayEleveId);
        $this->compterDuplicatas('liste', $this->selectDuplicatasParEleve($where));
    }

    private function compterDuplicatas($nature, $select)
    {
        $duplicatasParEleve = $this->renderResult($select);
        $tTarifs = $this->db_manager->get('Sbm\Db\Table\Tarifs');
        $detailDuplicatas = [];
        $nbDuplicatas = 0;
        foreach ($duplicatasParEleve as $row) {
            $nbDuplicatas += $row['duplicata'];
            $detailDuplicatas[$row['eleveId']] = [
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'grilleTarif' => $row['grilleTarif'],
                'duplicata' => $row['duplicata']
            ];
        }
        $montantDuplicatas = $tTarifs->getMontant($tTarifs->getDuplicataCodeGrille(),
            $nbDuplicatas);
        $this->resultats->setDuplicatas($nature,
            [
                'detailDuplicatas' => $detailDuplicatas,
                'montantDuplicatas' => $montantDuplicatas
            ]);
    }

    private function calculPaiementsResponsable(int $responsableId)
    {
        $as = sprintf('%d-%d', $this->millesime, $this->millesime + 1);
        $where = new Where();
        $where->equalTo('responsableId', $responsableId)->equalTo('anneeScolaire', $as);
        $tPaiements = $this->db_manager->get('Sbm\Db\Table\Paiements');
        $this->resultats->setPaiements($tPaiements->total($where));
    }

    private function selectAbonnementsElevesInscrits(Where $where)
    {
        $predicates = [
            $where,
            new Literal('gratuit = 0')
        ];
        $elevesPayantsInscrits = new Predicate\ElevesPayantsInscrits($this->millesime, 'sco',
            $predicates);
        return $this->selectAbonnements($elevesPayantsInscrits());
    }

    private function selectAbonnementsEleves(Where $where)
    {
        $predicates = [
            $where
        ];
        return $this->selectAbonnements(
            new Predicate\ElevesResponsablePayant($this->millesime, 'sco', $predicates));
    }

    private function selectAbonnements(Where $where)
    {
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
     * tableau d'indentifiants (eleveId, nom, prénom, grille tarifaire, nb de duplicatas)
     *
     * @param int $responsableId
     * @return \Zend\Db\Sql\Select
     */
    private function selectDuplicatasParEleve(Where $where)
    {
        $predicate = new Where([
            $where
        ], Where::COMBINED_BY_AND);
        $predicate->equalTo('millesime', $this->millesime);
        $this->addStrategy('grilleTarif',
            $this->db_manager->get('Sbm\Db\Table\Tarifs')
                ->getStrategie('grille'));
        return $this->sql->select(
            [
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ])
            ->columns([
            'eleveId' => 'eleveid',
            'nom',
            'prenom'
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId = sco.eleveId', [
            'duplicata',
            'grilleTarif'
        ])
            ->where($predicate);
    }
}