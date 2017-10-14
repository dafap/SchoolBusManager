<?php
/**
 * Injection des objets dans TransportController
 *
 * Préparation pour compatibilité avec ZF3
 * 
 * @project sbm
 * @package SbmGestion/Controller/Service
 * @filesource TransportControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 oct. 2017
 * @version 2017-2.3.12
 */
namespace SbmGestion\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmGestion\Controller\TransportController;
use SbmBase\Model\StdLib;

class TransportControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $config_controller = [
            'RenderPdfService' => $sm->get('RenderPdfService'),
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'cartographie_manager' => $sm->get('Sbm\CartographieManager'),
            'authenticate' => $sm->get('SbmAuthentification\Authentication'),
            'operateurs' => StdLib::getParamR([
                'sbm',
                'operateurs'
            ], $config_application),
            'mail_config' => StdLib::getParamR([
                'sbm',
                'mail'
            ], $config_application),
            'paginator_count_per_page' => StdLib::getParamR([
                'paginator',
                'count_per_page'
            ], $config_application)
        ];
        return new TransportController($config_controller);
    }
}