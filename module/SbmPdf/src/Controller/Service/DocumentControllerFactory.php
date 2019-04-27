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
 * @date 26 avr.. 2019
 * @version 2019-2.5.0
 */
namespace SbmPdf\Controller\Service;

use SbmBase\Model\StdLib;
use SbmFront\Model\Responsable\Service\ResponsableManager;
use SbmPdf\Controller\DocumentController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
            'responsable_manager' => $sm->get(ResponsableManager::class),
            'organisateur' => StdLib::getParamR([
                'sbm',
                'client'
            ], $config_application)
        ];
        return new DocumentController($config_controller);
    }
}