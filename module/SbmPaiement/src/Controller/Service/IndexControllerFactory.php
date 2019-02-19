<?php
/**
 * Injection des objets dans IndexController
 *
 * Préparation pour compatibilité avec ZF3
 * 
 * @project sbm
 * @package SbmPaiement/Controller/Service
 * @filesource IndexControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmPaiement\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmPaiement\Controller\IndexController;
use SbmBase\Model\StdLib;
use SbmFront\Model\Responsable\Service\ResponsableManager as Responsable;

class IndexControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $config_controller = [
            'RenderPdfService' => $sm->get('RenderPdfService'),
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'plugin_plateforme' => $sm->get('SbmPaiement\Plugin\Plateforme'),
            'responsable' => $sm->get(Responsable::class),
            'user' => $sm->get('SbmAuthentification\Authentication')
            ->by()
            ->getIdentity(),
            'paginator_count_per_page' => StdLib::getParamR([
                'paginator',
                'count_per_page'
            ], $config_application),
            'mail_config' => StdLib::getParamR([
                'sbm',
                'mail'
            ], $sm->get('config')),
            'csv' => StdLib::getParam('csv', $config_application)
        ];
        return new IndexController($config_controller);
    }
}
 