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
 * @date 30 juil. 2015
 * @version 2015-1
 */
namespace SbmPortail;

use ZfcBase\Module\AbstractModule;
use Zend\Mvc\MvcEvent;

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