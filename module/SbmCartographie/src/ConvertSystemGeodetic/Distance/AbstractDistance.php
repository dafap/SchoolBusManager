<?php
/**
 * Projection et méthode de conversion d'unités
 *
 * @project sbm
 * @package SbmCartographie/ConvertSystemGeodetic/Distance
 * @filesource AbstractDistance.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCartographie\ConvertSystemGeodetic\Distance;

use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use SbmCartographie\Model\Point;

abstract class AbstractDistance
{

    /**
     *
     * @var \SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface
     */
    protected $projection;

    public function setProjection(ProjectionInterface $projection): DistanceInterface
    {
        $this->projection = $projection;
        return $this;
    }

    protected function pointUniteEnRadians(Point $p)
    {
        $u1 = $p->getUnite();
        if (empty($u1)) {
            $p = $this->projection->xyzVersgRGF93($p)->to('radian');
        } elseif ($u1 != 'radian') {
            $p->to('radian');
        }
        return $p;
    }
}