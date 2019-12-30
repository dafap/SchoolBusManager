<?php
/**
 * Wgs84 ou gRGF93 (sans transformation)
 *
 * Projection neutre conservant les données en Wgs84
 *
 * @project sbm
 * @package SbmCartographie/ConvertSystemGeodetic/Projection
 * @filesource Wgs84.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 déc. 2019
 * @version 2019-2.5.4
 */

namespace SbmCartographie\ConvertSystemGeodetic\Projection;

use SbmCartographie\ConvertSystemGeodetic\Ellipsoide\Wgs84 as ellipsoideWgs84;
use SbmCartographie\Model\Point;

class Wgs84 extends AbstractProjection implements ProjectionInterface
{

    public function __construct()
    {
        $this->ellipsoide = new ellipsoideWgs84();
        $this->name = 'Wgs84';
        $this->unit = 'degré';
    }
    /**
     * Utiliser la méthode transforme pour conserver les attributs de $p
     *
     * {@inheritDoc}
     * @see \SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface::xyzVersgRGF93()
     */
    public function xyzVersgRGF93(Point $p)
    {
        return $p->transforme(new Point($p->getX(), $p->getY(), $p->getZ(), $this->unit));
    }

    /**
     * Utiliser la méthode transforme pour conserver les attributs de $p
     *
     * {@inheritDoc}
     * @see \SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface::gRGF93versXYZ()
     */
    public function gRGF93versXYZ(Point $p)
    {
        return $p->transforme(new Point($p->getX(), $p->getY(), $p->getZ()));
    }

}