<?php
/**
 * Interface pour les classes de projection
 *
 * Toutes les projections doivent implémenter cet interface afin de créer un plugin valable
 * 
 * @project sbm
 * @package SbmCartographie/ConvertSystemGeodetic/Projection
 * @filesource ProjectionInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCartographie\ConvertSystemGeodetic\Projection;

use SbmCartographie\Model\Point;

interface ProjectionInterface
{

    public function gRGF93versXYZ(Point $p);

    public function xyzVersgRGF93(Point $p);
}