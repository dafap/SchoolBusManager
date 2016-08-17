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
 * @date 17 août 2016
 * @version 2016-2.2.0
 */
namespace SbmBase\Module;

use Zend\ModuleManager\Feature\LocatorRegisteredInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

abstract class AbstractModule implements LocatorRegisteredInterface, AutoloaderProviderInterface
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
     * du projet. Ainsi, composer inclut automatiquement les références des classes dans 
     * le fichier vendor/composer/autoload_classmap.php
     * Il est donc tout à fait inutile de référencer à nouveau les classes dans chaque
     * module.
     * 
     * @return multitype:multitype:multitype:string
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    $this->getNamespace() => $this->getDir() . '/src/' . $this->getNamespace(),
                ),
            ),
        );
    }
    
    /**
     * Charge la configuration du module
     */
    public function getConfig()
    {
        return include $this->getDir() . '/config/module.config.php';
    }
}