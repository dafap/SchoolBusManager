<?php
/**
 * Module destiné aux partenaires et permettant la consultation des données autorisées
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmPortail
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmPortail;

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