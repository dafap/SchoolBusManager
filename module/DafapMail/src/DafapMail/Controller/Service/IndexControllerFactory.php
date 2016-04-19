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
 * @date 9 avr. 2016
 * @version 2016-2
 */
namespace DafapMail\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapMail\Controller\IndexController;
use SbmCommun\Model\StdLib;

class IndexControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'user' => $sm->get('Dafap\Authenticate')
                ->by()
                ->getIdentity(),
            'mail_config' => StdLib::getParamR([
                'sbm',
                'mail'
            ], $sm->get('config'))
        ];
        return new IndexController($config);
    }
}