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
 * @date 12 avr. 2016
 * @version 2016-2
 */
namespace SbmPdf\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmPdf\Controller\PdfController;

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