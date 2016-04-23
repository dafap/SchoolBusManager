<?php
/**
 * Injection des objets dans EleveController du module SbmAjax
 *
 * Préparation pour compatibilité avec ZF3
 * 
 * @project sbm
 * @package SbmAjax/Controller/Service
 * @filesource EleveControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 avr. 2016
 * @version 2016-2
 */
namespace SbmAjax\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmAjax\Controller\EleveController;
use SbmCommun\Model\StdLib;

class EleveControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'cartographie_manager' => $sm->get('Sbm\CartographieManager')
        ];
        return new EleveController($config_controller);
    }
}