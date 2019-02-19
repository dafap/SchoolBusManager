<?php
/**
 * Injection dans PdfListener des objets nécessaires
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmPdf/Listener/Service
 * @filesource PdfListenerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmPdf\Listener\Service;

use SbmPdf\Listener\PdfListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PdfListenerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new PdfListener($serviceLocator->get('Sbm\PdfManager'));
    }
}