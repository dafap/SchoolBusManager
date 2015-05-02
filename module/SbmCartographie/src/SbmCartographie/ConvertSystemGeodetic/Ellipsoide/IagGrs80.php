<?php
/**
 * IAG GRS 80 (Ellipsoïde associé au système RGF93)
 *
 * @project sbm
 * @package ConvetGeodetic/Model/Ellipsoide
 * @filesource IagGrs80.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 mars 2015
 * @version 2015-1
 */
namespace SbmCartographie\ConvertSystemGeodetic\Ellipsoide;

class IagGrs80 extends Ellipsoide 
{
    function __construct() 
    {
        $this->setA(6378137.0);
        $this->setF(298.257222101);
    }
}