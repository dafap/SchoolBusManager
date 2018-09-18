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
 * @date 13 avr. 2016
 * @version 2016-2
 */
namespace SbmPdf\Listener\Service;
 
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmPdf\Listener\PdfListener;

class PdfListenerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new PdfListener($serviceLocator->get('Sbm\PdfManager'));
    }
}