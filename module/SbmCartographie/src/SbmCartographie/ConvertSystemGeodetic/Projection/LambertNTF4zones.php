<?php
/**
 * Lambert zone 1 à 4
 *
 * Projection Lambert Connique Conforme Tangente
 * 
 * @project sbm
 * @package SbmCartographie/ConvertSystemGeodetic/Projection
 * @filesource LambertNTF4zones.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mars 2015
 * @version 2015-1
 */
namespace SbmCartographie\ConvertSystemGeodetic\Projection;

use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use SbmCartographie\ConvertSystemGeodetic\Ellipsoide\Clarke1880Ign;
use SbmCartographie\ConvertSystemGeodetic\Exception;
use SbmCartographie\Model\Point;

class LambertNTF4zones extends AbstractProjection implements ProjectionInterface
{

    public function __construct($nzone)
    {
        if (! is_int($nzone) || $nzone < 1 || $nzone > 4) {
            throw new Exception(__CLASS__ . " - Zone $nzone inconnue. Le numéro de zone doit être un entier compris entre 44 et 50.");
        }
        $this->ellipsoide = new Clarke1880Ign();
        $this->name = 'Lambert_Conformal_Conic_1SP';
        $this->epsg = "2757$nzone";
        $this->unit = 'grade';
        $this->central_meridian = 0;
        $this->primem = 2 + (20 + 14.025 / 60) / 60; // méridien de Paris (2°20'14,025")
        $this->paramsToWgs84 = array(
            - 168,
            - 60,
            320,
            0,
            0,
            0,
            0
        );
        switch ($nzone) {
            case 1:
                $this->latitude_of_origin = 55;
                $this->k0 = 0.999877341;
                $this->x0 = 600000.0;
                $this->y0 = 200000.0;
                
                break;
            case 2:
                $this->latitude_of_origin = 52;
                $this->k0 = 0.99987742;
                $this->x0 = 600000.0;
                $this->y0 = 200000.0;
                break;
            case 3:
                $this->latitude_of_origin = 49;
                $this->k0 = 0.999877499;
                $this->x0 = 600000.0;
                $this->y0 = 200000.0;
                break;
            case 4:
                $this->latitude_of_origin = 46.85;
                $this->k0 = 0.99994471;
                $this->x0 = 234.358;
                $this->y0 = 185861.369;
                break;
        }
        $this->alg0019();
    }

    /**
     * Reçoit un point en coordonnées géographiques (RGF93 ou WPS84) exprimées en degrés et renvoie un point en coordonnées de la zone
     *
     * @param Point $p
     *            longitude et latitude exprimées en degrés
     *            
     * @return \SbmCartographie\Model\Point
     */
    public function gRGF93versXYZ(Point $point)
    {
        // change de projection
        $proj = new Lambert93();
        // passe en coordonnées cartésiennes
        $pt = $proj->alg0009($point->getLongitude('radian'), $point->getLatitude('radian'));
        // applique la transformation 7 paramètres inverse (dont 4 nuls)
        $pt = $this->alg0013($pt, -$this->paramsToWgs84[0], -$this->paramsToWgs84[1], -$this->paramsToWgs84[2]);
        // passe en coordonnées géographiques NTF
        $pt = $this->alg0012($pt->getX(), $pt->getY(), $pt->getZ())
            ->to('grade');
        // passe en coordonnées XYZ
        return $this->alg0003($pt->getLongitude('radian'), $pt->getLatitude('radian'));
    }

    /**
     * Reçoit un point en coordonnées de la zone et renvoie un point en coordonnées géographiques (RGF93 ou WPS84) exprimées en degrés
     *
     * @param Point $p            
     * @return \SbmCartographie\Model\Point (les coordonnées du point résulat sont en degrés)
     */
    public function xyzVersgRGF93(Point $point)
    {
        // passe en coordonnées géographique (ellipsoïdales)
        $pt = $this->xyzVersgNTF($point);
        // passe en coordonnées cartésiennes
        $pt = $this->alg0009($pt->getLongitude('radian'), $pt->getLatitude('radian'));
        // applique la transformation 7 paramètres (dont 4 nuls)
        $pt = $this->alg0013($pt, $this->paramsToWgs84[0], $this->paramsToWgs84[1], $this->paramsToWgs84[2]);
        // change de projection
        $proj = new Lambert93();
        // passe en coordonnée géographiques RGF93
        return $proj->alg0012($pt->getX(), $pt->getY(), $pt->getZ())
            ->to('degré');
    }

    /**
     * Reçoit un point en coordonnées de la zone et renvoie un point en coordonnées géographiques (NTF) exprimées en grades
     *
     * @param Point $p            
     * @return \SbmCartographie\Model\Point (les coordonnées du point résulat sont en grades)
     */
    public function xyzVersgNTF(Point $point)
    {
        return $this->alg0004($point->getX(), $point->getY())
            ->to('grade');
    }

    /**
     * Reçoit un point en coordonnées géographiques (NTF) exprimées en grades et renvoie un point en coordonnées de la zone
     *
     * @param Point $p
     *            longitude et latitude exprimées en grades
     *            
     * @return \SbmCartographie\Model\Point
     */
    public function gNTFversXYZ(Point $point)
    {
        return $this->alg0003($point->getLongitude('radian'), $point->getLatitude('radian'));;
    }
}