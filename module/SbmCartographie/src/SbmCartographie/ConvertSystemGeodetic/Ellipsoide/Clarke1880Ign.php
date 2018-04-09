<?php
/**
 * Clarke 1880 IGN (Ellipsoïde associé au système NTF)
 *
 * @project sbm
 * @package ConvetGeodetic/Model/Ellipsoide
 * @filesource Clarke1880Ign.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCartographie\ConvertSystemGeodetic\Ellipsoide;

class Clarke1880Ign extends Ellipsoide
{

    function __construct()
    {
        $this->setA(6378249.2);
        $this->setB(6356515.0);
    }
}