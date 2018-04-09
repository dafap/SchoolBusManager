<?php
/**
 * Injecte les objets nécessaires dans le form DocumentPdf
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmPdf/Form/Service
 * @filesource DocumentPdfFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmPdf\Form\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmPdf\Service\PdfManager;
use SbmPdf\Form\DocumentPdf;
use SbmPdf\Model\Tcpdf;

class DocumentPdfFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof PdfManager)) {
            $message = 'PdfManager attendu. On a reçu un %s.';
            throw new Exception(sprintf($message, gettype($pdfManager)));
        }
        $db_manager = $serviceLocator->get('Sbm\DbManager');
        $auth_userId = $serviceLocator->get('SbmAuthentification\Authentication')
            ->by()
            ->getUserId();
        $pdf = $serviceLocator->get(Tcpdf::class);
        return new DocumentPdf($db_manager, $auth_userId, 
            $this->getTemplateMethodList($pdf));
    }

    private function getTemplateMethodList($pdf)
    {
        $methods = get_class_methods(Tcpdf::class);
        $list = [];
        foreach ($methods as $method) {
            if (preg_match('/template(.*)Method([0-9]*)/', $method, $matches)) {
                $list[strtolower($matches[1])][$matches[2]] = $pdf->{$method}('?');
            }
        }
        return $list;
    }
}