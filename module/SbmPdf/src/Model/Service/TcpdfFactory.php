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
 * @date 14 sept. 2018
 * @version 2016-2.4.5
 */
namespace SbmPdf\Model\Service;

use SbmPdf\Model\Exception;
use SbmPdf\Model\Tcpdf;
use SbmPdf\Service\PdfManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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