<?php
/**
 * Génère un pdf manager à partir de la configuration 'pdf_manager' des modules
 *
 * On peut accéder au db manager depuis le pdf manager par la clé 'Sbm\DbManager'.
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmPdf/Service
 * @filesource PdfManagerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2016
 * @version 2016-2.2.0
 */
namespace SbmPdf\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;
use SbmBase\Model\StdLib;

class PdfManagerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config_application = $serviceLocator->get('config');
        $controllers = array_merge(StdLib::getParamR([
            'controllers',
            'factories'
        ], $config_application, []), StdLib::getParamR([
            'controllers',
            'invokables'
        ], $config_application, []));
        asort($controllers);
        $pdf_manager = new PdfManager(new Config($serviceLocator->get('config')['pdf_manager']));
        $pdf_manager->setService('Sbm\DbManager', $serviceLocator->get('Sbm\DbManager'))
            ->setService('ViewRenderer', $serviceLocator->get('ViewRenderer'))
            ->setService('Dafap\Authenticate', $serviceLocator->get('Dafap\Authenticate'))
            ->setService('routes', $config_application['router']['routes'])
            ->setService('controllers', $controllers);
        return $pdf_manager;
    }
}