<?php
/**
 * Initialise le plugin Pdf en inhjectant le PdfManager
 *
 * @project sbm
 * @package SbmPdf/Mvc/Controller/Plugin
 * @filesource PdfFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 août 2021
 * @version 2021-2.6.3
 */
namespace SbmPdf\Mvc\Controller\Plugin;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PdfFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator;
        while (! $sm->has('Sbm\PdfManager')) {
            $sm = $sm->getServiceLocator();
        }
        return new Pdf($sm->get('Sbm\PdfManager'));
    }
}