<?php
/**
 * Lambert 93 
 *
 * Projection Lambert Connique Conforme Sécante
 * 
 * @project sbm
 * @package SbmCartographie/ConvertSystemGeodetic/Projection
 * @filesource Lambert93.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCartographie\ConvertSystemGeodetic\Projection;

use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use SbmCartographie\ConvertSystemGeodetic\Ellipsoide\IagGrs80;
use SbmCartographie\Model\Point;

class Lambert93 extends AbstractProjection implements ProjectionInterface
{

    public function __construct()
    {
        $this->ellipsoide = new IagGrs80();
        $this->name = 'Lambert_Conformal_Conic_2SP';
        $this->unit = 'degré';
        $this->central_meridian = 3;
        $this->latitude_of_origin = 46.5;
        $this->standard_parallel_1 = 44;
        $this->standard_parallel_2 = 49;
        $this->x0 = 700000.0;
        $this->y0 = 6600000.0;
        
        $this->alg0054();
    }

    /**
     * Reçoit un point en coordonnées géographiques (RGF93 ou WPS84) exprimées en degré et renvoie un point en coordonnées Lambert-93
     *
     * @param Point $p
     *            longitude et latitude exprimées en degré
     *            
     * @return \SbmCartographie\Model\Point
     */
    public function gRGF93versXYZ(Point $p)
    {
        return $p->transforme($this->alg0003($p->getLongitude('radian'), $p->getLatitude('radian')));
    }

    /**
     * Reçoit un point en coordonnées Lambert-93 et renvoie un point en coordonnées géographiques (RGF93 ou WPS84) exprimées en degré
     *
     * @param Point $p            
     * @return \SbmCartographie\Model\Point (les coordonnées du point en Lambert-93)
     */
    public function xyzVersgRGF93(Point $p)
    {
        return $p->transforme($this->alg0004($p->getX(), $p->getY())
            ->to('degré'));
    }
}