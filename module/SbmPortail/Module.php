<?php
/**
 * Module destiné aux partenaires et permettant la consultation des données autorisées
 * 
 * @project sbm
 * @package SbmPortail
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmPortail;

use SbmBase\Module\AbstractModule;
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