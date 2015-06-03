<?php
/**
 * Module de conversion de coordonnées géodésiques
 *
 * 
 * @project sbm
 * @package ConvertGeodetic
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 mars 2015
 * @version 2015-1
 */
namespace SbmCartographie;

use ZfcBase\Module\AbstractModule;

class Module extends AbstractModule
{

    public function getDir()
    {
        return __DIR__;
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }
}
 