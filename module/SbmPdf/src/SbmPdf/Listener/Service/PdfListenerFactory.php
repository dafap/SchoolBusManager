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
 * @date 5 avr. 2018
 * @version 2018-2.4.0
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