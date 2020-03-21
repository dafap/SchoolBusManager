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
 * @date 21 mars 2020
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
        $this->district = 0;
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
        return [
            'millesime' => $this->millesime,
            'eleveId' => $this->eleveId,
            'distanceR1' => $this->distanceR1,
            'distanceR2' => $this->distanceR2,
            'district' => $this->district
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
        $this->calculs();
        $oData = $this->getTable('Scolarites')->getObjData();
        $oData->exchangeArray($this->getData());
        $this->tScolarites->saveRecord($oData);
    }

    /**
     * Pour cette version de Arlysère, c'est la même chose
     *
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
        $this->majDistancesDistrict($eleveId, $gardeDistance);
    }

    private function calculs()
    {
        // charge la fiche élève pour disposer des responsableId
        $this->eleve = $this->getTable('eleves')->getRecord($this->eleveId);
        // charge la fiche scolarite pour disposer de l'établissement et de(s) la
        // station(s) demandée(s)
        $this->scolarite = $this->getTable('Scolarites')->getRecord(
            [
                'millesime' => $this->millesime,
                'eleveId' => $this->eleveId
            ]);
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
            // initialise le domicile du responsable 1
            // si l'élève a une adresse perso (localisation valide) elle remplace celle du
            // R1
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
        } catch (\Exception $e) {
            if (getenv('APPLICATION_ENV') == 'development') {
                throw new \Exception(__METHOD__, __LINE__, $e);
            } else {
                // @TODO: log dans le fichier des erreurs
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
        if ($pt->isValid()) {
            $pt->setAttribute('communeId', $this->scolarite->communeId);
            return $pt;
        }
        return false;
    }
}