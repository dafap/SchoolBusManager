<?php
/**
 * Service servant à calculer et à mettre en place les paramètres suivants :
 * - distanceR1
 * - distanceR2
 * - district
 *
 * Cette classe présente 3 méthodes publiques
 * - setMillesime() : sans paramètre elle met le millesime en session. Avec un paramètre sert à la simulation.
 * - majDistanceDistrict()
 * - majDistanceDistrictSansPerte()
 *
 *
 * @project sbm
 * @package SbmCommun/src/Arlysere
 * @filesource CalculDroits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCartographie\GoogleMaps;
use SbmCartographie\Model\Exception;
use SbmCartographie\Model\Point;
use SbmCartographie\Model\Projection;
use SbmCommun\Model\Paiements\GrilleTarifInterface;
use SbmCommun\Model\Traits\DebugTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CalculDroits implements FactoryInterface, GrilleTarifInterface
{
    use DebugTrait;

    /**
     *
     * @var int
     */
    private $millesime;

    /**
     *
     * @var int
     */
    private $eleveId;

    /**
     *
     * @var bool
     */
    private $gardeDistance;

    /**
     *
     * @var float
     */
    private $distanceR1;

    /**
     *
     * @var float
     */
    private $distanceR2;

    /**
     *
     * @var int
     */
    private $district;

    /**
     *
     * @var \SbmCommun\Model\Db\ObjectData\Eleve
     */
    private $eleve;

    /**
     *
     * @var \SbmCommun\Model\Db\ObjectData\Scolarite
     */
    private $scolarite;

    /**
     *
     * @var Point
     */
    private $ptEtablissement;

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     *
     * @var GoogleMaps\DistanceMatrix
     */
    private $oDistanceMatrix;

    /**
     *
     * @var Projection
     */
    private $oProjection;

    /**
     *
     * @var array
     */
    private $latRange;

    /**
     *
     * @var array
     */
    private $lngRange;

    /**
     *
     * @var array
     */
    private $compte_rendu;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! $serviceLocator->has(GoogleMaps\DistanceMatrix::class)) {
            throw new Exception\ExceptionNoCartographieManager(
                sprintf(_("CartographieManager attendu, doit contenir %s."),
                    GoogleMaps\DistanceMatrix::class));
        }
        $config_carte = StdLib::getParam('gestion', $serviceLocator->get('cartes'));
        $this->latRange = $config_carte['valide']['lat'];
        $this->lngRange = $config_carte['valide']['lng'];
        $this->db_manager = $serviceLocator->get('Sbm\DbManager');
        $this->oDistanceMatrix = $serviceLocator->get(GoogleMaps\DistanceMatrix::class);
        $this->oProjection = $serviceLocator->get(Projection::class);
        $this->compte_rendu = [];
        $this->setMillesime();
        $this->resetData();
        return $this;
    }

    /**
     * Dans le nom de la table sera donné soit en respectant la casse, soit en minuscule
     * pour les tables simples. Pour les tables de liaison comme RpiEtablissements, la
     * deuxième partie du nom devra respecteur la casse.
     *
     * @param string $nom_table
     * @return \SbmCommun\Model\Db\Service\Table\AbstractSbmTable
     */
    private function getTable(string $nom_table)
    {
        $nom_table = ucfirst($nom_table);
        return $this->db_manager->get("Sbm\\Db\\Table\\" . $nom_table);
    }

    private function resetData()
    {
        $this->eleveId = 0;
        $this->distanceR1 = 0.0;
        $this->distanceR2 = 0.0;
        $this->district = 1;
    }

    private function setDataFromScolarite()
    {
        $this->distanceR1 = $this->scolarite->distanceR1;
        $this->distanceR2 = $this->scolarite->distanceR2;
        $this->district = $this->scolarite->district;
    }

    /**
     *
     * @return number[]
     */
    private function getData()
    {
        return array_merge($this->scolarite->getArrayCopy(),
            [
                'distanceR1' => $this->distanceR1,
                'distanceR2' => $this->distanceR2,
                'district' => $this->district
            ]);
    }

    /**
     * initialise la propriété
     *
     * @param int $millesime
     */
    public function setMillesime(int $millesime = null)
    {
        if (is_null($millesime)) {
            $this->millesime = Session::get('millesime');
        } else {
            $this->millesime = $millesime;
        }
    }

    /**
     * Lance la mise à jour les tables scolarites et affectations pour cet élève : -
     * distanceR1 (sauf si gardeDistance) - distanceR2 (sauf si gardeDistance) - district
     * - grille tarifaire - affectations du matin (avec correspondances) - affectation du
     * soir (avec correspondances) - affectation du mercredi midi (avec correspondances)
     *
     * @param int $eleveId
     * @param bool $gardeDistance
     */
    public function majDistancesDistrict(int $eleveId, bool $gardeDistance = true)
    {
        $this->eleveId = $eleveId;
        $this->gardeDistance = $gardeDistance;
        $this->calculs();
        $oData = $this->getTable('Scolarites')->getObjData();
        $oData->exchangeArray($this->getData());
        $this->getTable('Scolarites')->saveRecord($oData);
    }

    /**
     * Pour cette version de Arlysère, c'est la même chose que majDistancesDistrict()
     *
     * @param int $eleveId
     * @param bool $gardeDistance
     */
    public function majDistancesDistrictSansPerte(int $eleveId, bool $gardeDistance = true)
    {
        $this->majDistancesDistrict($eleveId, $gardeDistance);
    }

    /**
     * NON UTILISE DANS CETTE VERSION. Uniquement pour la compatibilité du code.
     *
     * @param array $row
     *            tableau décrivant la scolarité d'un élève avec au moins les champs
     *            suivants : distanceR1, distanceR2, district, derogation
     * @return boolean
     */
    public function estEnAttente($row)
    {
        return false;
    }

    /**
     * . NON UTILISE DANS CETTE VERSION. Le compte-rendu renvoyé est un tableau de la
     * forme :
     *
     * @formatter off
     *  []
     *  ou ['message' => string]
     *  ou [
     *        'etablissements' => array
     *     ] où array est un tableau de ['nom' => string, 'commune'=>string]
     * @formatter on
     *
     * @return array
     */
    public function getCompteRendu()
    {
        return $this->compte_rendu;
    }

    private function calculs()
    {
        // charge la fiche élève pour disposer des responsableId
        $this->eleve = $this->getTable('eleves')->getRecord($this->eleveId);
        // charge la fiche scolarite pour disposer de l'établissement et de(s) la
        // station(s) demandée(s)
        try {
            $this->scolarite = $this->getTable('Scolarites')->getRecord(
                [
                    'millesime' => $this->millesime,
                    'eleveId' => $this->eleveId
                ]);
        } catch (\Exception $e) {
            $this->compte_rendu[] = $e->getMessage();
            $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/logs'), 'sbm_error.log');
            $this->debugLog($e->getMessage());
            $this->debugLog($e->getTraceAsString());
        }
        $this->setDataFromScolarite();
        // calcul des distances si on ne les garde pas ou si elles ne sont pas connues
        if (! $this->gardeDistance ||
            ($this->scolarite->demandeR1 && $this->scolarite->distanceR1 == 0) ||
            ($this->scolarite->demandeR1 && $this->scolarite->distanceR1 == 99) ||
            ($this->scolarite->demandeR2 && $this->scolarite->distanceR2 == 0) ||
            ($this->scolarite->demandeR2 && $this->scolarite->distanceR2 == 99)) {
            // initialise l'établissement
            $ptEtablissement = $this->getPtEtablissement(
                $this->scolarite->etablissementId);
            // initialise le domicile du responsable 1 si l'élève a une adresse perso
            // (localisation valide) elle remplace celle du R1
            $ptDomicileR1 = $this->getPtDomicilePerso();
            if (! $ptDomicileR1) {
                $ptDomicileR1 = $this->getPtDomicileResponsable(
                    $this->eleve->responsable1Id);
            }
            // initialise éventuellement le domicile du responsable 2 (pareil)
            try {
                $ptDomicileR2 = $this->getPtDomicileResponsable(
                    $this->eleve->responsable2Id);
            } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
                $ptDomicileR2 = null;
            }
            $this->setDistances($ptEtablissement, $ptDomicileR1, $ptDomicileR2);
        }
        // ici le district est toujours 1
        $this->district = 1;
    }

    /**
     *
     * @param \SbmCartographie\Model\Point $ptEtablissement
     * @param \SbmCartographie\Model\Point $ptDomicileR1
     * @param \SbmCartographie\Model\Point|null $ptDomicileR2
     * @throws \Exception
     */
    private function setDistances(Point $ptEtablissement, Point $ptDomicileR1,
        $ptDomicileR2)
    {
        try {
            $result = $this->oDistanceMatrix->plusieursOriginesUneDestination(
                array_filter([
                    $ptDomicileR1,
                    $ptDomicileR2
                ]), $ptEtablissement);
            $this->distanceR1 = round((current($result) ?: 0) / 1000, 1);
            if ($ptDomicileR2) {
                $this->distanceR2 = round((next($result) ?: 0) / 1000, 1);
            }
        } catch (GoogleMaps\Exception\ExceptionNoAnswer $e) {
            // GoogleMaps ne répond pas
            if ($this->scolarite->demandeR1) {
                $this->distanceR1 = 99.0;
            } else {
                $this->distanceR1 = 0.0;
            }
            if ($this->scolarite->demandeR2) {
                $this->distanceR2 = 99.0;
            } else {
                $this->distanceR2 = 0.0;
            }
            $this->compte_rendu[] = 'GoogleMaps ne répond pas';
            $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/logs'), 'sbm_error.log');
            $this->debugLog('GoogleMaps ne répond pas');
        } catch (\Exception $e) {
            if (getenv('APPLICATION_ENV') == 'development') {
                throw new \Exception(__METHOD__, __LINE__, $e);
            } else {
                $this->compte_rendu[] = $e->getMessage();
                $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/logs'), 'sbm_error.log');
                $this->debugLog($e->getMessage());
                $this->debugLog($e->getTraceAsString());
            }
        }
    }

    private function getPtEtablissement(string $etablissementId)
    {
        $etablissement = $this->getTable('Etablissements')->getRecord($etablissementId);
        return (new Point($etablissement->x, $etablissement->y))->setAttribute(
            'etablissementId', $etablissement->etablissementId)
            ->setAttribute('classeId', $this->scolarite->classeId)
            ->setAttribute('regimeId', $this->scolarite->regimeId)
            ->setAttribute('communeId', $etablissement->communeId)
            ->setAttribute('statut', $etablissement->statut);
    }

    private function getPtDomicileResponsable($responsableId)
    {
        $responsable = $this->getTable('Responsables')->getRecord($responsableId);
        return (new Point($responsable->x, $responsable->y))->setAttribute('communeId',
            $responsable->communeId);
    }

    private function getPtDomicilePerso()
    {
        $pt = new Point($this->scolarite->x, $this->scolarite->y);
        if ($this->oProjection->isValid($pt, 'gestion')) {
            $pt->setAttribute('communeId', $this->scolarite->communeId);
            return $pt;
        }
        return false;
    }
}