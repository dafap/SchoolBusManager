<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource DocumentControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 oct. 2016
 * @version 2016-2.2.1
 */
namespace SbmPdf\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmPdf\Controller\DocumentController;
use SbmFront\Model\Responsable\Service\ResponsableManager as Responsable;

class DocumentControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'pdf_manager' => $sm->get('Sbm\PdfManager'),
            'authenticate' => $sm->get('SbmAuthentification\Authentication'),
            'responsable' => $sm->get(Responsable::class),
        ];
        return new DocumentController($config_controller);
    }
}