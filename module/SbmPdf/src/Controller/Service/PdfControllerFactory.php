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
 * @date 8 fév. 2019
 * @version 2019-2.5.0
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
            'RenderPdfService' => $sm->get('RenderPdfService'),
            'db_manager' => $sm->get('Sbm\DbManager'),
            'pdf_manager' => $sm->get('Sbm\PdfManager')
        ];
        return new PdfController($config_controller);
    }
}