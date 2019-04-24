<?php
/**
 * Calcul de distance entre deux points
 *
 * @project sbm
 * @package SbmCartographie/ConvertSystemGeodetic/Distance
 * @filesource DistanceInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCartographie\ConvertSystemGeodetic\Distance;

use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use SbmCartographie\Model\Point;

interface DistanceInterface
{

    /**
     * Renvoie la distance entre les points donnés
     *
     * @param \SbmCartographie\Model\Point $point1
     * @param \SbmCartographie\Model\Point $point2
     *
     * @return float
     */
    public function getDistance(Point $point1, Point $point2): float;

    public function setProjection(ProjectionInterface $projection): DistanceInterface;
}