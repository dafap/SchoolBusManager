<?php
/**
 * Service servant à calculer et à mettre en place les paramètres suivants :
 * - distanceR1
 * - distanceR2
 * - district
 * - grilleTarifR1
 * - reductionR1
 * - grilleTarifR2
 * - reductionR2
 *
 * Cette classe présente 3 méthodes publiques
 * - setMillesime()
 * - majDistanceDistrict()
 * - majDistanceDistrictSansPerte()
 *
 *
 * @project sbm
 * @package SbmCommun/src/Arlysere
 * @filesource CalculDroits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCartographie\GoogleMaps;
use SbmCartographie\Model\Exception;
use SbmCommun\Model\Paiements\GrilleTarifInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmCartographie\Model\Point;

class CalculDroits implements FactoryInterface, GrilleTarifInterface
{

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
     * @var float
     */
    private $district;

    /**
     *
     * @var int
     */
    private $grilleTarifR1;

    /**
     *
     * @var int
     */
    private $grilleTarifR2;

    /**
     *
     * @var bool
     */
    private $reductionR1;

    /**
     *
     * @var int
     */
    private $reductionR2;

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
     * @var bool
     */
    private $periode_reduction;

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
     * @var array
     */
    private $etat_du_site;

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
        $tCalendar = $this->db_manager->get('Sbm\Db\System\Calendar');
        $this->etat_du_site = $tCalendar->getEtatDuSite();
        $this->periode_reduction = $this->etat_du_site['etat'] == $tCalendar::ETAT_PENDANT;
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
        return $this->db_manager->get("Sbm\\Db\\Table\\Scolarites\\" . $nom_table);
    }

    private function resetData()
    {
        $this->eleveId = 0;
        $this->distanceR1 = 0.0;
        $this->distanceR2 = 0.0;
        $this->district = 0;
        $this->grilleTarifR1 = 0; // self::HORS_ARLYSERE;
        $this->reductionR1 = 0;
        $this->grilleTarifR2 = 0;
        $this->reductionR2 = 0;
    }

    private function setDataFromScolarite()
    {
        $this->distanceR1 = $this->scolarite->distanceR1;
        $this->distanceR2 = $this->scolarite->distanceR2;
        $this->district = $this->scolarite->district;
        $this->grilleTarifR1 = $this->scolarite->grilleTarifR1;
        $this->grilleTarifR2 = $this->scolarite->grilleTarifR2;
        $this->reductionR1 = $this->scolarite->reductionR1;
        $this->reductionR2 = $this->scolarite->reductionR2;
    }

    /**
     *
     * @return number[]
     */
    private function getData()
    {
        return [
            'millesime' => $this->millesime,
            'eleveId' => $this->eleveId,
            'distanceR1' => $this->distanceR1,
            'distanceR2' => $this->distanceR2,
            'district' => $this->district,
            'grilleTarifR1' => $this->grilleTarifR1,
            'reductionR1' => $this->reductionR1,
            'grilleTarifR2' => $this->grilleTarifR2,
            'reductionR2' => $this->reductionR2
        ];
    }

    /**
     * initialise la propriété
     *
     * @param int $millesime
     */
    public function setMillesime(int $millesime = null)
    {
        if (is_null($millesime)) {
            $this->data['millesime'] = Session::get('millesime');
        } else {
            $this->data['millesime'] = $millesime;
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
    }

    /**
     * Lance la mise à jour les tables scolarites et affectations pour cet élève : -
     * distanceR1 (sauf si gardeDistance) - distanceR2 (sauf si gardeDistance) - district
     * (s'il est à 0, sinon on garde 1 quoi qu'il en soit) - grille tarifaire -
     * affectations du matin (avec correspondances) - affectation du soir (avec
     * correspondances) - affectation du mercredi midi (avec correspondances)
     *
     * @param int $eleveId
     * @param bool $gardeDistance
     */
    public function majDistancesDistrictSansPerte(int $eleveId, bool $gardeDistance = true)
    {
        $this->eleveId = $eleveId;
        $this->gardeDistance = $gardeDistance;
    }

    private function calculs()
    {
        // charge la fiche élève pour disposer de la date de creation et des responsableId
        $this->eleve = $this->getTable('eleves')->getRecord($this->eleveId);
        // charge la fiche scolarite pour disposer de l'établissement et de la station
        // demandée
        $this->scolarite = $this->getTable('scolarites')->getRecord(
            [
                'millesime' => $this->millesime,
                'eleveId' => $this->eleveId
            ]);
        $this->setDataFromScolarite();
        // détermine le droit au tarif réduit
        if ($this->periode_reduction || $this->estPremiereInscription()) {
            $this->reduction = true;
        } else {
            $this->reduction = false;
        }
        // initialise l'établissement et indique quelle grille appliquer pour Arlysere
        $ptEtablissement = $this->getPtEtablissement($this->scolarite->etablissementId);
        // initialise le domicile du responsable 1 en vérifiant s'il est dans Arlysere
        // si l'élève a une adresse perso (localisation valide) elle remplace celle du R1
        $ptDomicileR1 = $this->getPtDomicilePerso();
        if (! $ptDomicileR1) {
            $ptDomicileR1 = $this->getPtDomicileResponsable($this->eleve->responsable1Id);
        }
        // initialise éventuellement le domicile du responsable 2 (pareil)
        try {
            $ptDomicileR2 = $this->getPtDomicileResponsable($this->eleve->responsable2Id);
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            $ptDomicileR2 = null;
        }
        // calcul des distances si on ne les garde pas ou si elles ne sont pas connues
        if (! $this->gardeDistance ||
            ($this->scolarite->demandeR1 && $this->scolarite->distanceR1 == 0) ||
            ($this->scolarite->demandeR1 && $this->scolarite->distanceR1 == 99) ||
            ($this->scolarite->demandeR2 && $this->scolarite->distanceR2 == 0) ||
            ($this->scolarite->demandeR2 && $this->scolarite->distanceR2 == 99)) {
            $this->setDistances($ptEtablissement, $ptDomicileR1, $ptDomicileR2);
        }
        // recherche un trajet


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
                $this->distanceR1 = round((next($result) ?: 0) / 1000, 1);
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
        } catch (\Exception $e) {
            if (getenv('APPLICATION_ENV') == 'development') {
                throw new \Exception(__METHOD__, __LINE__, $e);
            } else {
                // @TODO: log dans le fichier des erreurs
            }
        }
    }

    /**
     * Renvoie le numéro de la grille tarifaire à appliquer pour les résidents d'Arlysère
     *
     * @param string $etablissementId
     * @return number
     */
    private function getTarifRpi(string $etablissementId)
    {
        try {
            $oRpiEtablissement = $this->getTable('rpiEtablissements')->getRecord(
                $etablissementId);
            $oRpi = $this->getTable('rpi')->getRecord($oRpiEtablissement->rpiId);
            return $oRpi->grille;
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            return self::TARIF_ARLYSERE;
        }
    }

    private function getPtEtablissement(string $etablissementId)
    {
        $etablissement = $this->getTable('etablissement')->getRecord($etablissementId);
        return (new Point($etablissement->x, $etablissement->y))->setAttribute(
            'etablissementId', $etablissement->etablissementId)
            ->setAttribute('classeId', $this->scolarite->classeId)
            ->setAttribute('regimeId', $this->scolarite->regimeId)
            ->setAttribute('communeId', $etablissement->communeId)
            ->setAttribute('statut', $etablissement->statut)
            ->setAttribute('grille', $this->getTarifRpi($etablissementId));
    }

    private function getPtDomicileResponsable($responsableId)
    {
        $responsable = $this->getTable('responsables')->getRecord($responsableId);
        return (new Point($responsable->x, $responsable->y))->setAttribute('communeId',
            $responsable->communeId)
            ->setAttribute('demenagement', $responsable->demenagement)
            ->setAttribute('dansArlysere', $this->dansArlysere($responsable->communeId));
    }

    private function getPtDomicilePerso()
    {
        $pt = new Point($this->scolarite->x, $this->scolarite->y);
        if ($pt->isValid()) {
            $pt->setAttribute('communeId', $this->scolarite->communeId)
                ->setAttribute('demenagement', false)
                ->setAttribute('dansArlysere',
                $this->dansArlysere($this->scolarite->communeId));
            return $pt;
        }
        return false;
    }

    private function estPremiereInscription()
    {
        $dateCreation = \DateTime::createFromFormat('Y-m-d', $this->eleve->dateCreation);
        return $dateCreation > $this->etat_du_site['echeance'];
    }

    /**
     * Indique si la commune est dans Arlysere
     *
     * @param string $communeId
     * @return bool
     */
    private function dansArlysere(string $communeId)
    {
        return $this->getTable('communes')->getRecord($communeId)->membre;
    }
}