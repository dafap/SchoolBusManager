<?php
/**
 * Méthodes nécessaires à la localisation d'une station
 *
 * @project sbm
 * @package SbmGestion\Model\Localisation
 * @filesource Station.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 sept. 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\Localisation;

use SbmBase\Model\StdLib;
use SbmCartographie\GoogleMaps;
use SbmCartographie\Model\Point;
use SbmCommun\Form\LatLng as FormLatLng;

class Station
{

    /**
     *
     * @var \Zend\ServiceManager\ServiceManager
     */
    private $cartographie_manager;

    /**
     * Les clés sont 'lat' et 'lng'
     *
     * @var array
     */
    private $centre;

    /**
     *
     * @var array
     */
    private $config;

    /**
     *
     * @var \SbmCommun\Model\Db\ObjectData\Commune
     */
    private $ocommune;

    /**
     *
     * @var \SbmCommun\Model\Db\ObjectData\Station
     */
    private $ostation;

    /**
     * Contient les services passant par la station courante
     *
     * @var array
     */
    private $currentStationServices;

    /**
     *
     * @var array
     */
    private $arrayPtStations;

    /**
     *
     * @var \SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface
     */
    private $projection;

    /**
     *
     * @var string
     */
    private $url_api;

    public function __construct($cartographie_manager)
    {
        $this->cartographie_manager = $cartographie_manager;
        $this->config = StdLib::getParam('station', $cartographie_manager->get('cartes'));
        $this->projection = $cartographie_manager->get(GoogleMaps\DistanceMatrix::class)->getProjection();
        $this->url_api = $cartographie_manager->get('google_api_browser')['js'];
        $this->setCentre(0, 0);
        $this->ostation = null;
        $this->ocommune = null;
        $this->arrayPtStations = [];
    }

    public function getForm(bool $btnsubmit, $cas = 'localisation'): \Zend\Form\Form
    {
        if ($cas == 'ajout') {
            $key = 'phase';
            $value = 1;
        } else {
            $key = 'stationId';
            $value = [
                'id' => 'stationId'
            ];
        }
        if ($btnsubmit) {
            $buttons = [
                'submit' => [
                    'class' => 'button default submit left-95px',
                    'value' => 'Enregistrer la localisation'
                ],
                'cancel' => [
                    'class' => 'button default cancel left-10px',
                    'value' => 'Abandonner'
                ]
            ];
        } else {
            $buttons = [
                'cancel' => [
                    'class' => 'button default cancel left-10px',
                    'value' => 'Abandonner'
                ]
            ];
        }
        return new FormLatLng(
            [
                $key => $value,
                'lat' => [
                    'id' => 'lat'
                ],
                'lng' => [
                    'id' => 'lng'
                ]
            ], $buttons, $this->config['valide']);
    }

    public function getCentreLat(): float
    {
        return $this->centre['lat'];
    }

    public function getCentreLng(): float
    {
        return $this->centre['lng'];
    }

    /**
     * En cas de succès, renvoie un tableau contenant le 'numero', la 'rue', le
     * 'lieu-dit', la 'commune'
     *
     * @param number $lat
     * @param number $lng
     * @return array
     */
    public function getLieu($lat, $lng): array
    {
        return $this->cartographie_manager->get(GoogleMaps\Geocoder::class)->reverseGeocoding(
            $lat, $lng);
    }

    public function getPointEnRGF93($x, $y): Point
    {
        $pt = new Point($x, $y);
        return $this->projection->xyzVersgRGF93($pt);
    }

    public function getPointEnXYZ($lat, $lng): Point
    {
        $pt = new Point($lng, $lat, 0, 'degré');
        return $this->projection->gRGF93versXYZ($pt);
    }

    public function getUrlApi(): string
    {
        return $this->url_api;
    }

    public function getZoom(int $n = 0): int
    {
        return $this->config['zoom'] + $n;
    }

    public function initCurrentStation(int $stationId): void
    {
        $db_manager = $this->cartographie_manager->get('Sbm\DbManager');
        $this->ostation = $db_manager->get('Sbm\Db\Table\Stations')->getRecord($stationId);
        $this->ocommune = $db_manager->get('Sbm\Db\table\Communes')->getRecord(
            $this->ostation->communeId);
        if ($this->ostation->x == 0.0 && $this->ostation->y == 0.0) {
            // essaie de trouver le lieu par geocoder et vérifie s'il est dans la zone
            $sa = new \SbmCommun\Filter\SansAccent();
            $gmapcommune = $sa->filter($this->ocommune->alias);
            $geocode_result = $this->cartographie_manager->get(GoogleMaps\Geocoder::class)->geocode(
                $this->ostation->nom, $this->ocommune->codePostal, $gmapcommune);
            $pt = new Point($geocode_result['lng'], $geocode_result['lat'], 0, 'degré');
            $pt->setLatLngRange($this->config['valide']['lat'],
                $this->config['valide']['lng']);
            if ($pt->isValid()) {
                $this->setCentre($pt->getLatitude(), $pt->getLongitude());
            } else {
                $this->setCentre(0, 0);
                $pt->setLatitude($this->getCentreLat());
                $pt->setLongitude($this->getCentreLng());
            }
        } else {
            $pt = $this->getPointEnRGF93($this->ostation->x, $this->ostation->y);
            $this->setCentre($pt->getLatitude(), $pt->getLongitude());
        }
        $this->arrayPtStations = [];
        $this->currentStationServices = [];
        foreach ($db_manager->get('Sbm\Db\Query\Stations')->getArrayDesserteStations() as $autreStation) {
            if ($autreStation->stationId != $stationId) {
                $this->arrayPtStations[] = $this->getPointEnRGF93($autreStation->x,
                    $autreStation->y)->setAttribute('station', $autreStation);
            } else {
                $this->currentStationServices = $autreStation->services;
            }
        }
    }

    public function getCurrentStationArray()
    {
        return [
            $this->ostation->nom,
            $this->ocommune->codePostal . ' ' . $this->ocommune->alias
        ];
    }

    public function getCurrentStationDescription()
    {
        return sprintf('<b>%s</b><br>%s %s', $this->ostation->nom,
            $this->ocommune->codePostal, $this->ocommune->alias);
    }

    public function getCurrentStationServices()
    {
        return $this->currentStationServices;
    }

    public function getPtStations()
    {
        return $this->arrayPtStations;
    }

    public function setCentre($lat, $lng): self
    {
        if ($lat == 0 && $lng == 0) {
            $this->centre['lat'] = $this->config['centre']['lat'];
            $this->centre['lng'] = $this->config['centre']['lng'];
        } else {
            $this->centre['lat'] = $lat;
            $this->centre['lng'] = $lng;
        }
        return $this;
    }
}