<?php
/**
 * Injecte les objets nécessaires dans la classe Columns
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmPdf/Model/Service
 * @filesource ColumnsFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmPdf\Model\Service;

use SbmPdf\Model\Columns;
use SbmPdf\Model\Exception;
use SbmPdf\Service\PdfManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ColumnsFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof PdfManager)) {
            $message = 'PdfManager attendu. On a reçu un %s.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $db_manager = $serviceLocator->get('Sbm\DbManager');
        $auth_userId = $serviceLocator->get('SbmAuthentification\Authentication')
            ->by()
            ->getUserId();
        return new Columns($db_manager, $auth_userId);
    }
}