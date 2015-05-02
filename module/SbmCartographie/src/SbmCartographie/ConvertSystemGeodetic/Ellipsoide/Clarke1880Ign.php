<?php
/**
 * Clarke 1880 IGN (Ellipsoïde associé au système NTF)
 *
 * @project sbm
 * @package ConvetGeodetic/Model/Ellipsoide
 * @filesource Clarke1880Ign.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 mars 2015
 * @version 2015-1
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