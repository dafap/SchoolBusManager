<?php
/**
 * Injection des objets dans PdfController
 *
 * Compatibilité ZF3
 *
 * @project sbm
 * @package SbmPdf/Controller/Service
 * @filesource PdfControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 nov. 2021
 * @version 2021-2.6.4
 */
namespace SbmPdf\Controller\Service;

use SbmPdf\Controller\PdfController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PdfControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'pdf_manager' => $sm->get('Sbm\PdfManager')
        ];
        return new PdfController($config_controller);
    }
}