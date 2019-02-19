<?php
/**
 * Utilisation de l'API GoogleMaps distanceMatrix pour calculer des distances
 * 
 * @project sbm
 * @package SbmCartographie/GoogleMaps
 * @filesource DistanceMatrix.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 août 2018
 * @version 2018-2.4.3
 */
namespace SbmCartographie\GoogleMaps;

use SbmCartographie\Model\Point;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;

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
     * @var \resource
     */
    private $context;

    /**
     *
     * @var SbmCartographie\Projection\ProjectionInterface
     */
    private $projection;

    public function __construct($projection, $google_api_distanceMatrix, $scheme = null)
    {
        $this->projection = $projection;
        $this->google_distancematrix_url = $google_api_distanceMatrix;
        if (is_null($scheme) || $scheme == 'http') {
            $this->context = null;
        } else {
            $cafile = __DIR__ . '/../../../config/cacert.pem';
            $this->context = stream_context_create(
                [
                    'ssl' => [
                        'cafile' => $cafile
                    ]
                ]);
        }
    }

    /**
     * Centralisation des appels à l'API
     *
     * @param array(Point)|Point $origines            
     * @param array(Point)|Point $destinations            
     *
     * @return mixed
     * @see json_decode
     */
    public function getJsonResult($origines, $destinations)
    {
        return json_decode(
            file_get_contents(
                $this->getUrlGoogleApiDistanceMatrix($origines, $destinations), false, 
                $this->context));
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
     * @return string
     */
    private function getUrlGoogleApiDistanceMatrix($origines, $destinations)
    {
        try {
            $url = sprintf($this->google_distancematrix_url, 
                $this->getLatLngFromParams($origines), 
                $this->getLatLngFromParams($destinations));
            return $url;
        } catch (Exception $e) {
            ob_start();
            var_dump($origines);
            $sOrigines = html_entity_decode(strip_tags(ob_get_clean()));
            ob_start();
            var_dump($destinations);
            $sDestinations = html_entity_decode(strip_tags(ob_get_clean()));
            $msg = sprintf("%s(%s, %s) : %s", __METHOD__, $sOrigines, $sDestinations, 
                $e->getTraceAsString());
            throw new Exception($msg, 0, $e);
        }
    }

    /**
     * Renvoie une chaine de caractères paramètre de l'api distanceMatrix
     *
     * @param array(Point)|Point $points            
     * @throws Exception
     * @return string
     */
    public function getLatLngFromParams($points)
    {
        if (is_array($points)) {
            $sLatLngParam = $this->buildLatLngFromArray($points);
        } elseif ($points instanceof Point) {
            $sLatLngParam = $this->buildLatLngFromPoint($points);
        } else {
            throw new Exception(
                'Type incorrect : SbmCartographie\Model\Point ou tableau de SbmCartographie\Model\Point attendu. On a reçu un ' .
                     gettype($points));
        }
        return $sLatLngParam;
    }

    /**
     *
     * @param array(Point) $aPoint            
     * @throws Exception
     * @return string
     */
    private function buildLatLngFromArray($aPoint)
    {
        $array = [];
        foreach ($aPoint as $point) {
            if (! $point instanceof Point) {
                throw new Exception(
                    'Type incorrect dans un tableau : SbmCartographie\Model\Point attendu. On a reçu un ' .
                         gettype($point));
            }
            $array[] = $this->buildLatLngFromPoint($point);
        }
        return implode('|', $array);
    }

    /**
     * Renvoie une chaine formée par 'lat,lng' où lat et lng sont la latitude et la longitude en degrés décimaux.
     *
     * @param SbmCartographie\Model\Point $point            
     *
     * @return string
     */
    private function buildLatLngFromPoint(Point $point)
    {
        if (! in_array($point->getUnite(), 
            [
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
     * Renvoie la distance en mètres
     *
     * @param Point $origine            
     * @param Point $destination            
     * @return number
     */
    public function calculDistance(Point $origine, Point $destination)
    {
        $d = null;
        $obj = $this->getJsonResult($origine, $destination);
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
            throw new ExceptionNoAnswer($msg);
        }
    }

    /**
     * Il y a plusieurs origines et une seule destination.
     * Renvoie un tableau de distances des origines à la destination.
     *
     * @param array(Point) $origines
     *            tableau de Point
     * @param Point $destination            
     *
     * @throws Exception
     * @return array tableau de distances
     */
    public function plusieursOriginesUneDestination(array $origines, Point $destination)
    {
        if (! is_array($origines)) {
            throw new Exception(
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
            throw new ExceptionNoAnswer($msg);
        }
    }

    /**
     * Il y a une seule origine et plusieurs destinations.
     * La réponse de l'API donne donc un seul rows et plusieurs elements dans ce rows.
     * Renvoie un tableau de distances de l'origine aux destinations
     *
     * @param Point $origine            
     * @param array(Point) $destinations
     *            tableau de Point
     * @throws Exception
     * @return array tableau de distances
     */
    public function uneOriginePlusieursDestinations(Point $origine, array $destinations)
    {
        if (! is_array($destinations)) {
            throw new Exception(
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
            throw new ExceptionNoAnswer($msg);
        }
    }
}