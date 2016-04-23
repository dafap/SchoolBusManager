<?php
/**
 * Injection dans Tcpdf des objets nécessaires et du PdfManager
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmPdf/Model/Service
 * @filesource TcpdfFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 avr. 2016
 * @version 2016-2
 */
namespace SbmPdf\Model\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmPdf\Model\Tcpdf;
use SbmPdf\Model\Carte;
use SbmPdf\Model\Label;
use SbmPdf\Model\Exception;
use SbmPdf\Service\PdfManager;

class TcpdfFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $pdfManager)
    {
        if (! ($pdfManager instanceof PdfManager)) {
            $message = 'PdfManager attendu. On a reçu un %s.';
            throw new Exception(sprintf($message, gettype($pdfManager)));
        }
        return new Tcpdf($pdfManager);        
    }
}