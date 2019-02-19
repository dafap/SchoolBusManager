<?php
/**
 * Lambert 2006 CC42 à Lambert 2006 CC50 (9 zones)
 *
 * Projection Lambert Connique Conforme Sécante
 *
 * @project sbm
 * @package SbmCartographie/ConvertSystemGeodetic/Projection
 * @filesource Lambert06CC9zones.php
 *             @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmCartographie\ConvertSystemGeodetic\Projection;

use SbmCartographie\ConvertSystemGeodetic\Exception;
use SbmCartographie\ConvertSystemGeodetic\Ellipsoide\IagGrs80;
use SbmCartographie\Model\Point;

class Lambert06CC9zones extends AbstractProjection implements ProjectionInterface
{

    public function __construct($nzone)
    {
        if (! is_int($nzone) || $nzone < 42 || $nzone > 50) {
            throw new Exception\DomainException(
                __CLASS__ .
                " - Zone $nzone inconnue. Le numéro de zone doit être un entier compris entre 42 et 50.");
        }
        $this->ellipsoide = new IagGrs80();
        $this->name = 'Lambert_Conformal_Conic_2SP';
        $this->epsg = "39$nzone";
        $this->unit = 'degré';
        $this->central_meridian = 3;
        $this->latitude_of_origin = $nzone;
        $this->standard_parallel_1 = $nzone - 0.75;
        $this->standard_parallel_2 = $nzone + 0.75;
        $this->x0 = 1700000.0;
        $this->y0 = ($nzone - 41) * 1000000 + 200000.0;
        $this->alg0054();
    }

    /**
     * Reçoit un point en coordonnées géographiques (RGF93 ou WPS84) exprimées en degré et renvoie
     * un point en coordonnées de la zone
     *
     * @param Point $p
     *            longitude et latitude exprimées en degré
     *            
     * @return \SbmCartographie\Model\Point
     */
    public function gRGF93versXYZ(Point $p)
    {
        return $p->transforme(
            $this->alg0003($p->getLongitude('radian'), $p->getLatitude('radian')));
    }

    /**
     * Reçoit un point en coordonnées de la zone et renvoie un point en coordonnées géographiques
     * (RGF93 ou WPS84) exprimées en degré
     *
     * @param Point $p
     * @return \SbmCartographie\Model\Point (les coordonnées du point résulat sont en degré)
     */
    public function xyzVersgRGF93(Point $p)
    {
        return $p->transforme($this->alg0004($p->getX(), $p->getY())
            ->to('degré'));
    }
}