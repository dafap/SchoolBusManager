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
 * @date 18 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmBase\Module;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\LocatorRegisteredInterface;

abstract class AbstractModule implements AutoloaderProviderInterface,
    ConfigProviderInterface, LocatorRegisteredInterface
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
     * Ne doit pas faire référence à DIR/autoload_classmap.php
     * En effet, les modules seront déclarés dans la section autoload du composer.json
     * du projet.
     * Ainsi, composer inclut automatiquement les références des classes dans
     * le fichier vendor/composer/autoload_classmap.php
     * Il est donc tout à fait inutile de référencer à nouveau les classes dans chaque
     * module.
     *
     * @return multitype:multitype:multitype:string
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    $this->getNamespace() => $this->getDir() . '/src/' .
                    $this->getNamespace()
                ]
            ]
        ];
    }

    /**
     * Charge la configuration du module
     */
    public function getConfig()
    {
        return include $this->getDir() . '/../config/module.config.php';
    }
}