<?php
/**
 * Injection des objets dans IndexController
 *
 * Préparation pour compatibilité avec ZF3
 * 
 * @project sbm
 * @package DafapMail/Controller/Service
 * @filesource IndexControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2016
 * @version 2016-2.2.0
 */
namespace DafapMail\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapMail\Controller\IndexController;
use SbmBase\Model\StdLib;

class IndexControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $config = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'user' => $sm->get('Dafap\Authenticate')
                ->by()
                ->getIdentity(),
            'mail_config' => StdLib::getParamR([
                'sbm',
                'mail'
            ], $config_application),
            'img' => StdLib::getParamR([
                'sbm',
                'img'
            ], $config_application),
            'client' => StdLib::getParamR([
                'sbm',
                'client'
            ], $config_application)
        ];
        return new IndexController($config);
    }
}