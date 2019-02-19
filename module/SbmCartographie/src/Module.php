<?php
/**
 * Module de conversion de coordonnées géodésiques
 *
 * 
 * @project sbm
 * @package SbmCartographie
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmCartographie;

use SbmBase\Module\AbstractModule;

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
 