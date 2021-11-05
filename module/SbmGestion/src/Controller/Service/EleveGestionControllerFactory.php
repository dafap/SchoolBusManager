<?php
/**
 * Injection des objets dans EleveGestionController
 *
 * Préparation pour compatibilité avec ZF3
 *
 * @project sbm
 * @package SbmGestion/Controller/Service
 * @filesource EleveGestionControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 oct. 2021
 * @version 2021-2.6.4
 */
namespace SbmGestion\Controller\Service;

use SbmBase\Model\StdLib;
use SbmGestion\Controller\EleveGestionController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EleveGestionControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'cartographie_manager' => $sm->get('Sbm\CartographieManager'),
            'authenticate' => $sm->get('SbmAuthentification\Authentication'),
            'mail_config' => StdLib::getParamR([
                'sbm',
                'mail'
            ], $config_application),
            'paginator_count_per_page' => StdLib::getParamR(
                [
                    'paginator',
                    'count_per_page'
                ], $config_application)
        ];
        return new EleveGestionController($config_controller);
    }
}