<?php
/**
 * Utilisation de l'API GoogleMaps distanceMatrix pour calculer des distances
 *
 * @project sbm
 * @package SbmCartographie/GoogleMaps
 * @filesource DistanceMatrix.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmCartographie\GoogleMaps;

use SbmBase\Model\StdLib;
use SbmCartographie\Model\Point;

class DistanceMatrix
{

    /**
     * URL d'appel de l'API distanceMatrix
     *
     * @var string
     */
    private $google_distancematrix_url;

    /**
     * Contexte de flux nécessaire pour les appels ssl
     *
     * @var resource
     */
    private $context;

    /**
     *
     * @var \SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface
     */
    private $projection;

    public function __construct($projection, $google_api_distanceMatrix)
    {
        $this->projection = $projection;
        $this->google_distancematrix_url = $google_api_distanceMatrix;
        $cafile = StdLib::concatPath(StdLib::findParentPath(__DIR__, 'config/ssl'),
            'cacert.pem');
        $this->context = stream_context_create([
            'ssl' => [
                'cafile' => $cafile
            ]
        ]);
    }

    /**
     * Centralisation des appels à l'API
     *
     * @param array(Point)|Point $origines
     * @param array(Point)|Point $destinations
     *
     * @return mixed
     *
     * @link http://www.php.net/manual/en/function.json-decode.php
     */
    public function getJsonResult($origines, $destinations, $walking = false)
    {
        $url_api = $this->getUrlGoogleApiDistanceMatrix($origines, $destinations);
        if ($walking) {
            $url_api = str_replace('mode=car', 'mode=walking', $url_api);
        }
        return json_decode(file_get_contents($url_api, false, $this->context));
    }

    /**
     * Renvoie l'objet Projection configuré
     *
     * @return \SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface
     */
    public function getProjection()
    {
        return $this->projection;
    }

    /**
     * Renvoie l'url de l'API GoogleMaps distanceMatrix construite avec les paramètres
     * $origines et $destinations.
     *
     * @param array(Point)|Point $origines
     * @param array(Point)|Point $destinations
     *
     * @throws \SbmCartographie\GoogleMaps\Exception\Exception
     *
     * @return string
     */
    private function getUrlGoogleApiDistanceMatrix($origines, $destinations)
    {
        try {
            $url = sprintf($this->google_distancematrix_url,
                $this->getLatLngFromParams($origines),
                $this->getLatLngFromParams($destinations));
            return $url;
        } catch (Exception\InvalidArgumentException $e) {
            ob_start();
            var_dump($origines);
            $sOrigines = html_entity_decode(strip_tags(ob_get_clean()));
            ob_start();
            var_dump($destinations);
            $sDestinations = html_entity_decode(strip_tags(ob_get_clean()));
            $msg = sprintf("%s(%s, %s) : %s", __METHOD__, $sOrigines, $sDestinations,
                $e->getTraceAsString());
            throw new Exception\Exception($msg, 0, $e);
        }
    }

    /**
     * Renvoie une chaine de caractères paramètre de l'api distanceMatrix
     *
     * @param array(Point)|Point $points
     *
     * @throws \SbmCartographie\GoogleMaps\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function getLatLngFromParams($points)
    {
        if (is_array($points)) {
            $sLatLngParam = $this->buildLatLngFromArray($points);
        } elseif ($points instanceof Point) {
            $sLatLngParam = $this->buildLatLngFromPoint($points);
        } else {
            throw new Exception\InvalidArgumentException(
                'Type incorrect : SbmCartographie\Model\Point ou tableau de SbmCartographie\Model\Point attendu. On a reçu un ' .
                gettype($points));
        }
        return $sLatLngParam;
    }

    /**
     *
     * @param array(Point) $aPoint
     *
     * @throws \SbmCartographie\GoogleMaps\Exception\InvalidArgumentException
     *
     * @return string
     */
    private function buildLatLngFromArray($aPoint)
    {
        $array = [];
        foreach ($aPoint as $point) {
            if (! $point instanceof Point) {
                throw new Exception\InvalidArgumentException(
                    'Type incorrect dans un tableau : SbmCartographie\Model\Point attendu. On a reçu un ' .
                    gettype($point));
            }
            $array[] = $this->buildLatLngFromPoint($point);
        }
        return implode('|', $array);
    }

    /**
     * Renvoie une chaine formée par 'lat,lng' où lat et lng sont la latitude et la
     * longitude en degrés décimaux.
     *
     * @param \SbmCartographie\Model\Point $point
     *
     * @return string
     */
    private function buildLatLngFromPoint(Point $point)
    {
        if (! in_array($point->getUnite(), [
            'degré',
            'grade',
            'radian'
        ])) {
            $p = $this->projection->xyzVersgRGF93($point);
        } else {
            $p = $point;
        }
        $p = $p->to('degré');
        return sprintf('%s,%s', number_format($p->getLatitude(), 6, '.', ''),
            number_format($p->getLongitude(), 6, '.', ''));
    }

    /**
     * Renvoie la distance en mètres.
     * Pour les distances inférieure à 1500 m on calcule aussi la distance à pied
     * et on renvoie la plus petite
     *
     * @param Point $origine
     * @param Point $destination
     *
     * @throws \SbmCartographie\GoogleMaps\Exception\ExceptionNoAnswer
     *
     * @return number
     */
    public function calculDistance(Point $origine, Point $destination)
    {
        $d = $this->lanceCalcul($origine, $destination) ;
        if ($d < 1500) {
            $w = $this->lanceCalcul($origine, $destination, true);
            if ($w < $d) {
                return $w;
            }
        }
        return $d;
    }
    private function lanceCalcul(Point $origine, Point $destination, bool $walking = false)
    {
        $d = null;
        $obj = $this->getJsonResult($origine, $destination, $walking);
        if ($obj) {
            if ($obj->status == 'OK') {

                if ($obj->rows[0]->elements[0]->status == 'OK') {
                    $d = $obj->rows[0]->elements[0]->distance->value;
                } else {
                    $msg = $obj->rows[0]->elements[0]->status;
                }
            } else {
                $msg = $obj->status;
            }
        } else {
            $msg = 'NO_ANSWER';
        }
        if ($d) {
            return $d;
        } else {
            throw new Exception\ExceptionNoAnswer($msg);
        }
    }

    /**
     * Il y a plusieurs origines et une seule destination. Renvoie un tableau de distances
     * des origines à la destination.
     *
     * @param array(Point) $origines
     *            tableau de Point
     * @param Point $destination
     *
     * @throws \SbmCartographie\GoogleMaps\Exception\ExceptionInterface
     * @throws \SbmCartographie\GoogleMaps\Exception\ExceptionNoAnswer
     *
     * @return array tableau de distances
     */
    public function plusieursOriginesUneDestination(array $origines, Point $destination)
    {
        if (! is_array($origines)) {
            throw new Exception\InvalidArgumentException(
                __METHOD__ .
                ' - Le paramètre 1 de cette méthode doit être un tableau. On a reçu ' .
                gettype($origines));
        }
        $result = [];
        $obj = $this->getJsonResult($origines, $destination);
        if ($obj) {
            if ($obj->status == 'OK') {
                for ($j = 0; $j < count($obj->rows); $j ++) {
                    if ($obj->rows[$j]->elements[0]->status == 'OK') {
                        $result[] = $obj->rows[$j]->elements[0]->distance->value;
                    } else {
                        $msg = $obj->rows[$j]->elements[0]->status;
                    }
                }
            } else {
                $msg = $obj->status;
            }
        } else {
            $msg = 'NO_ANSWER';
        }
        if ($result) {
            return $result;
        } else {
            throw new Exception\ExceptionNoAnswer($msg);
        }
    }

    /**
     * Il y a une seule origine et plusieurs destinations. La réponse de l'API donne donc
     * un seul rows et plusieurs elements dans ce rows. Renvoie un tableau de distances de
     * l'origine aux destinations
     *
     * @param Point $origine
     * @param array(Point) $destinations
     *            tableau de Point
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ExceptionNoAnswer
     *
     * @return array tableau de distances
     */
    public function uneOriginePlusieursDestinations(Point $origine, array $destinations)
    {
        if (! is_array($destinations)) {
            throw new Exception\InvalidArgumentException(
                __METHOD__ .
                ' - Le paramètre 2 de cette méthode doit être un tableau. On a reçu ' .
                gettype($destinations));
        }
        $result = [];
        $obj = $this->getJsonResult($origine, $destinations);
        if ($obj) {
            if ($obj->status == 'OK') {
                for ($j = 0; $j < count($obj->rows[0]->elements); $j ++) {
                    if ($obj->rows[0]->elements[$j]->status == 'OK') {
                        $result[] = $obj->rows[0]->elements[$j]->distance->value;
                    } else {
                        $msg = $obj->rows[0]->elements[$j]->status;
                    }
                }
            } else {
                $msg = $obj->status;
            }
        } else {
            $msg = 'NO_ANSWER';
        }
        if ($result) {
            return $result;
        } else {
            throw new Exception\ExceptionNoAnswer($msg);
        }
    }
}