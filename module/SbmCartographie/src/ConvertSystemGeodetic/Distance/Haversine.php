<?php
/**
 * Calcul de distance par approximation sur une sphère
 *
 * Usage :
 * $oDistance = new Haversine();
 * $distance = $oDistance->setProjection($p)->getDistance($p1, $p2);
 *
 * @project sbm
 * @package SbmCartographie/ConvertSystemGeodetic/Distance
 * @filesource Haversine.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCartographie\ConvertSystemGeodetic\Distance;

use SbmCartographie\Model\Point;

class Haversine extends AbstractDistance implements DistanceInterface
{

    /**
     * Calcule de distance par la méthode d'approximation sur une sphère dont le rayon est
     * le rayon moyen de l'ellipsoïde
     *
     * {@inheritdoc}
     * @see \SbmCartographie\ConvertSystemGeodetic\Distance\DistanceInterface::getDistance()
     */
    public function getDistance(Point $point1, Point $point2): float
    {
        $point1 = $this->pointUniteEnRadians($point1);
        $point2 = $this->pointUniteEnRadians($point2);
        $lat1 = $point1->getLatitude('radian');
        $lat2 = $point2->getLatitude('radian');
        $dLat = $lat1 - $lat2;
        $lon1 = $point1->getLongitude('radian');
        $lon2 = $point2->getLongitude('radian');
        $dLon = $lon1 - $lon2;
        $rayon = $this->projection->getEllipsoide()->getRayonMoyen();
        $arg = sqrt(
            (sin($dLat / 2) ** 2) + cos($lat1) * cos($lat2) * (sin($dLon / 2) ** 2));
        $distance = 2 * $rayon * asin($arg);
        return round($distance, 3);
    }
}