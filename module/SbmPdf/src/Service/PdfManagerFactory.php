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
 * @date 7 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmPdf\Service;

use SbmBase\Model\StdLib;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PdfManagerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config_application = $serviceLocator->get('config');
        $controllers = array_merge(
            StdLib::getParamR([
                'controllers',
                'factories'
            ], $config_application, []),
            StdLib::getParamR([
                'controllers',
                'invokables'
            ], $config_application, []));
        asort($controllers);
        $pdf_manager = new PdfManager(
            new Config($serviceLocator->get('config')['pdf_manager']));
        $pdf_manager->setService('Sbm\DbManager', $serviceLocator->get('Sbm\DbManager'))
            ->setService('ViewRenderer', $serviceLocator->get('ViewRenderer'))
            ->setService('SbmAuthentification\Authentication',
            $serviceLocator->get('SbmAuthentification\Authentication'))
            ->setService('routes', $config_application['router']['routes'])
            ->setService('controllers', $controllers);
        return $pdf_manager;
    }
}