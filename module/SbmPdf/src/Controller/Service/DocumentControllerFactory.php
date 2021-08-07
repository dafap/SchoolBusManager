<?php
/**
 * Injection des objets dans DocumentController du module SbmPdf
 *
 *
 * @project sbm
 * @package SbmPdf/src/Controller/Service
 * @filesource DocumentControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 août 2021
 * @version 2021-2.6.3
 */
namespace SbmPdf\Controller\Service;

use SbmBase\Model\StdLib;
use SbmFront\Model\Responsable\Service\ResponsableManager;
use SbmPdf\Controller\DocumentController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DocumentControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $config_controller = [

            'db_manager' => $sm->get('Sbm\DbManager'),
            'pdf_manager' => $sm->get('Sbm\PdfManager'),
            'authenticate' => $sm->get('SbmAuthentification\Authentication'),
            'responsable_manager' => $sm->get(ResponsableManager::class),
            'organisateur' => StdLib::getParamR([
                'sbm',
                'client'
            ], $config_application)
        ];
        return new DocumentController($config_controller);
    }
}