<?php
/**
 * Factory pour un ThemeJs
 *
 *
 * @project sbm
 * @package SbmBase/Model/View/Helper
 * @filesource ThemeJsFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmBase\Model\View\Helper;

use SbmBase\Model\StdLib;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ThemeJsFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $viewhelperManager)
    {
        $serviceLocator = $viewhelperManager->getServiceLocator();
        $config_application = $serviceLocator->get('config');
        $sigle = StdLib::getParamR([
            'sbm',
            'client',
            'sigle'
        ], $config_application);
        if ($sigle == 'SBM') {
            $sigle = 'default';
        }
        return new ThemeJs($sigle);
    }
}
