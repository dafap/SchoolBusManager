<?php
/**
 * International HAYFORD 1909 (Ellipsoïde associé au système ED50)
 *
 * @project sbm
 * @package ConvetGeodetic/Model/Ellipsoide
 * @filesource InternationalHayford1909.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
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