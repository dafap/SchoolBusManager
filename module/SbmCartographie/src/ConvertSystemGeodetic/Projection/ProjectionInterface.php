<?php
/**
 * Interface pour les classes de projection
 *
 * Toutes les projections doivent implémenter cet interface afin de créer un plugin
 * valable
 *
 * @project sbm
 * @package SbmCartographie/ConvertSystemGeodetic/Projection
 * @filesource ProjectionInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCartographie\ConvertSystemGeodetic\Projection;

use SbmCartographie\ConvertSystemGeodetic\Ellipsoide\Ellipsoide;
use SbmCartographie\Model\Point;

interface ProjectionInterface
{

    public function getEllipsoide(): Ellipsoide;

    public function gRGF93versXYZ(Point $p);

    public function xyzVersgRGF93(Point $p);
}