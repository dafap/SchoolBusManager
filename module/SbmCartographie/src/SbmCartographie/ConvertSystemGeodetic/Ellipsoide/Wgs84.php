<?php
/**
 * WGS84 (Ellipsoïde associé au système WGS84)
 *
 * @project sbm
 * @package ConvetGeodetic/Model/Ellipsoide
 * @filesource Wgs84.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCartographie\ConvertSystemGeodetic\Ellipsoide;

class Wgs84 extends Ellipsoide
{

    function __construct()
    {
        $this->setA(6378137.0);
        $this->setF(298.257223563);
    }
}