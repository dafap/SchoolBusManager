<?php
/**
 * Module SbmGestion pour le service de gestion des transports scolaires
 * - gestion des comptes des utilisateurs
 * - gestion du réseau de transport
 * - gestion des élèves
 * - gestion financière
 * - importations et exporation de données
 *
 * @project sbm
 * @package module/SbmGestion
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2016
 * @version 2016-2.2.0
 */
namespace SbmGestion;

use SbmBase\Module\AbstractModule;
use Zend\EventManager\EventInterface;
use Zend\Mvc\ModuleRouteListener;
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
