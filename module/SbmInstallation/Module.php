<?php
/**
 * Module SbmInstallation pour créer les tables de la base de données, les requêtes enregistrées
 * - création des tables
 * - création des requêtes enregistrées
 * - 
 *
 * @project sbm
 * @package module/SbmInstallation
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2016
 * @version 2016-2.2.0
 */
namespace SbmInstallation;

use SbmBase\Module\AbstractModule;
use Zend\Stdlib\Glob;
use Zend\Config\Factory as ConfigFactory;
use Zend\Mvc\MvcEvent;

class Module extends AbstractModule
{

    public function onBootstrap(MvcEvent $e)
    {
        set_time_limit(600);
        ini_set('memory_limit', '-1');
    }

    public function getDir()
    {
        return __DIR__;
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }
}
