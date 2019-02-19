<?php
/**
 * Factory pour un JQuery
 *
 * Initialise le tableau de configuration des librairies
 * 
 * @project sbm
 * @package SbmBase/Model/View/Helper
 * @filesource JQueryFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 févr. 2019
 * @version 2019-2.4.7
 */
namespace SbmBase\Model\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmBase\Model\StdLib;

class JQueryFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $viewhelperManager)
    {
        $serviceLocator = $viewhelperManager->getServiceLocator();
        $config_application = $serviceLocator->get('config');
        return new JQuery(StdLib::getParam('jquery', $config_application));
    }
}