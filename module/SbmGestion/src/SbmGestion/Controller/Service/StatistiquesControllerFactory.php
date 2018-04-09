<?php
/**
 * Injection des objets dans StatistiquesController
 *
 * Préparation pour compatibilité avec ZF3
 * 
 * @project sbm
 * @package SbmGestion/Controller/Service
 * @filesource StatistiquesControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmGestion\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmGestion\Controller\StatistiquesController;
use SbmBase\Model\StdLib;

class StatistiquesControllerFactory implements FactoryInterface
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
            'mail_config' => StdLib::getParamR(
                [
                    'sbm',
                    'mail'
                ], $config_application),
            'paginator_count_per_page' => StdLib::getParamR(
                [
                    'paginator',
                    'count_per_page'
                ], $config_application)
        ];
        return new StatistiquesController($config_controller);
    }
}