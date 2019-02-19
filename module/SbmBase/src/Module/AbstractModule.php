<?php
/**
 * Classe abstraite contenant le modèle de chargement pour tous les modules du projet.
 * Toutes les classes du projet seront enregistrées dans le module_manager.
 * 
 * @project sbm
 * @package SbmBase\Module
 * @filesource AbstractModule.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 fév.2019
 * @version 2019-2.5.0
 */
namespace SbmBase\Module;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\LocatorRegisteredInterface;

abstract class AbstractModule implements ConfigProviderInterface,
    LocatorRegisteredInterface
{

    /**
     * Renvoie le DIR de la classe dérivée.
     *
     * @return string
     */
    abstract public function getDir();

    /**
     * Renvoie le NAMESPACE de la classe dérivée
     *
     * @return string
     */
    abstract public function getNamespace();

    /**
     * Charge la configuration du module
     */
    public function getConfig()
    {
        return include $this->getDir() . '/../config/module.config.php';
    }
}