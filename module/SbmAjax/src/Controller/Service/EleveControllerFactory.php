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
 * @date 29 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmAjax\Controller\Service;

use SbmAjax\Controller\EleveController;
use SbmBase\Model\StdLib;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EleveControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $config_controller = [
            
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'cartographie_manager' => $sm->get('Sbm\CartographieManager'),
            'img' => StdLib::getParamR(
                [
                    'sbm',
                    'img'
                ], $config_application)
        ];
        return new EleveController($config_controller);
    }
}