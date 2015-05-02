<?php
/**
 * International HAYFORD 1909 (Ellipsoïde associé au système ED50)
 *
 * @project sbm
 * @package ConvetGeodetic/Model/Ellipsoide
 * @filesource InternationalHayford1909.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 mars 2015
 * @version 2015-1
 */
namespace SbmCartographie\ConvertSystemGeodetic\Ellipsoide;

class InternationalHayford1909 extends Ellipsoide
{
    function __construct()
    {
        $this->setA(6378388.0);
        $this->setF(297.0);
    }
}