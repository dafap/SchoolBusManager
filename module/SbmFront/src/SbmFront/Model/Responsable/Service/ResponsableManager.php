<?php
/**
 * Crée un service manager qui permet de demander la création d'un Responsable
 *
 * Compatibilité ZF3.
 * Le responsable n'est créé que si l'on en a besoin.
 * 
 * @project sbm
 * @package SbmFront/Model/Responsable/Service
 * @filesource ResponsableManager.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2016
 * @version 2016-1
 */
namespace SbmFront\Model\Responsable\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class ResponsableManager implements FactoryInterface
{
    /**
     * 
     * @var \Zend\ServiceManager\ServiceManager
     */
    private $responsable_manager;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->responsable_manager = new ServiceManager();
        $this->responsable_manager->setFactory(Responsable::class, ResponsableFactory::class);
        $this->responsable_manager->setService('Dafap\Authenticate', $serviceLocator->get('Dafap\Authenticate'));
        $this->responsable_manager->setService('Sbm\DbManager', $serviceLocator->get('Sbm\DbManager'));
        return $this;
    }
    
    /**
     * Renvoie l'instance du responsable
     */
    public function get()
    {
        return $this->responsable_manager->get(Responsable::class);
    }
} 