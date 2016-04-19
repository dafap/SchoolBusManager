<?php
/**
 * Injection des objets dans LoginController
 *
 * Préparation pour compatibilité avec ZF3
 * 
 * @project sbm
 * @package SbmFront/Controller/Service
 * @filesource LoginControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 avr. 2016
 * @version 2016-2
 */
namespace SbmFront\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmFront\Controller\LoginController;
use SbmFront\Model\Responsable\Responsable;
use SbmCommun\Model\StdLib;

class LoginControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $cm = $sm->get('Sbm\CartographieManager');
        $config_application = $sm->get('config');
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'authenticate' => $sm->get('Dafap\Authenticate'),
            'responsable' => $sm->get(Responsable::class),
            'distance_etablissements' => $cm->get('SbmCarto\DistanceEtablissements'),
            'config_cartes' => $cm->get('cartes'),
            'mail_config' => StdLib::getParamR([
                'sbm',
                'mail'
            ], $config_application)
        ];
        return new LoginController($config_controller);
    }
}