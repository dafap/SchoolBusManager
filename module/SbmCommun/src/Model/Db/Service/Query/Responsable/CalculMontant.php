<?php
/**
 * Méthodes calculant les montants payés et à payer par un responsable
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Responsable
 * @filesource CalculMontant.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 juil. 2021
 * @version 2021-2.6.3
 */
namespace SbmCommun\Model\Db\Service\Query\Responsable;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use SbmCommun\Model\Db\Sql\Predicate;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Where;

class CalculMontant extends AbstractQuery
{

    /**
     *
     * @var array
     */
    private $abonnements;

    /**
     *
     * @var array
     */
    private $duplicatas;

    protected function init()
    {
        $this->abonnements = [];
        $this->duplicatas = [];
    }

    /**
     * Pour un responsableId on renvoie un tableau ayant les clés suivantes : <ul>
     * <li>'montantAbonnements' est le montant total du par le responsable pour les
     * abonnements</li><li>'detailAbonnements' est un tableau qui donne, pour chaque code
     * de grille, le nom de la grille, la quantite (nombre d'enfants à prendre en compte)
     * et le montant total à facturer au titre de cette grille</li>
     *
     * @param int $responsableId
     * @param array|null $aEleveId
     *            tableau d'identifiants d'élèves
     * @return array
     */
    public function getAbonnementsResponsable1(int $responsableId, array $aEleveId = null)
    {
        if (empty($this->abonnements)) {
            $effectifsParGrilleTarif = $this->renderResult(
                $this->selectEffectifsParGrilleTarif($responsableId, $aEleveId));
            $tTarifs = $this->db_manager->get('Sbm\Db\Table\Tarifs');
            $detailAbonnements = [];
            $montantAbonnements = 0;
            foreach ($effectifsParGrilleTarif as $row) {
                $montantGrille = $tTarifs->getMontant($row['grilleCodeR1'],
                    $row['quantite'], $this->millesime);
                $detailAbonnements[$row['grilleCodeR1']] = [
                    'grille' => $row['grilleTarifR1'],
                    'reduction' => $row['reductionR1'],
                    'quantite' => $row['quantite'],
                    'montant' => $montantGrille
                ];
                $montantAbonnements += $montantGrille;
            }
            $this->abonnements = [
                'detailAbonnements' => $detailAbonnements,
                'montantAbonnements' => $montantAbonnements
            ];
        }
        return $this->abonnements;
    }

    /**
     * Si on passe un tableau d'identifiants d'élèves, la requête sera construite à partir
     * de ce tableau et ne tiendra pas compte du responsableId
     *
     * @param int $responsableId
     * @param array|null $aEleveid
     *            tableau d'identifiants d'élèves
     * @return \Zend\Db\Sql\Select
     */
    protected function selectEffectifsParGrilleTarif(int $responsableId, $aEleveid)
    {
        if (is_null($aEleveid)) {
            $where = new Where(null, Where::COMBINED_BY_OR);
            $where->equalTo('responsable1Id', $responsableId)->equalTo('responsable2Id',
                $responsableId);
        } else {
            $where = new Where();
            $where->in('ele.eleveId', $aEleveid);
        }
        $predicate = new Predicate\ElevesResponsablePayant($this->millesime, 'sco',
            [
                $where
            ], Where::COMBINED_BY_AND);
        $this->addStrategy('grilleTarifR1',
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
                'grilleTarifR1' => 'grilleTarifR1',
                'grilleCodeR1' => 'grilleTarifR1',
                'reductionR1' => 'reductionR1',
                'grilleCodeR2' => 'grilleTarifR2',
                'reductionR2' => 'reductionR2'
            ])
            ->columns([
            'quantite' => new Literal('count(*)')
        ])
            ->where($predicate())
            ->group('grilleCodeR1');
    }

    public function getDuplicatasResponsable1(int $responsableId)
    {
        if (empty($this->duplicatas)) {
            $duplicatasParEleve = $this->renderResult(
                $this->selectDuplicatasParEleve($responsableId));
            $tTarifs = $this->db_manager->get('Sbm\Db\Table\Tarifs');
            $detailDuplicatas = [];
            $nbDuplicatas = 0;
            foreach ($duplicatasParEleve as $row) {
                $nbDuplicatas += $row['duplicataR1'];
                $detailDuplicatas[$row['eleveId']] = [
                    'nom' => $row['nom'],
                    'prenom' => $row['prenom'],
                    'grilleTarifR1' => $row['grilleTarifR1'],
                    'grilleCodeR1' => $row['grilleCodeR1'],
                    'reductionR1' => $row['reductionR1'],
                    'grilleCodeR2' => $row['grilleCodeR2'],
                    'reductionR2' => $row['reductionR2'],
                    'duplicataR1' => $row['duplicataR1']
                ];
            }
            $montantDuplicatas = $tTarifs->getMontant($tTarifs->getDuplicataCodeGrille(),
                $nbDuplicatas, $this->millesime);
            $this->duplicatas = [
                'detailDuplicatas' => $detailDuplicatas,
                'montantDuplicatas' => $montantDuplicatas
            ];
        }
        return $this->duplicatas;
    }

    /**
     * Renvoie la liste des élèves d'un responsable (nom, prénom, grille tarifaire, nb de
     * duplicatas)
     *
     * @param int $responsableId
     * @return \Zend\Db\Sql\Select
     */
    protected function selectDuplicatasParEleve(int $responsableId)
    {
        $where = new Where(null, Where::COMBINED_BY_OR);
        $where->equalTo('responsable1Id', $responsableId)->equalTo('responsable2Id',
            $responsableId);
        $predicate = new Where([
            $where
        ], Where::COMBINED_BY_AND);
        $predicate->equalTo('millesime', $this->millesime);
        $this->addStrategy('grilleTarifR1',
            $this->db_manager->get('Sbm\Db\Table\Tarifs')
                ->getStrategie('grille'));
        return $this->sql->select(
            [
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ])
            ->columns([
            'eleveId' => 'eleveId',
            'nom',
            'prenom'
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId = sco.eleveId',
            [
                'duplicataR1',
                'duplicataR2',
                'grilleTarifR1',
                'grilleCodeR1' => 'grilleTarifR1',
                'reductionR1',
                'grilleCodeR2' => 'grilleTarifR2',
                'reductionR2'
            ])
            ->where($predicate);
    }

    /**
     * Renvoie le montant total du par un responsable pour le millesime en cours. Si on
     * passe un tableau d'identifiants d'élèves, ce seront ces élèves qui seront pris en
     * compte pour les abonnements. Par contre, pour les duplicatas, c'est bien le
     * responsableId qui est pris en compte.
     *
     * @param int $responsableId
     * @param array $aEleveId
     * @return float
     */
    public function calculMontantTotal(int $responsableId, array $aEleveId)
    {
        if (empty($aEleveId)) {
            $aEleveId = [
                - 1
            ]; // pour compatibilité de la clause IN de MySql
        }
        $montant = $this->getAbonnementsResponsable1($responsableId, $aEleveId)['montantAbonnements'];
        $montant += $this->getDuplicatasResponsable1($responsableId)['montantDuplicatas'];
        return $montant;
    }
}