<?php
/**
 * Injection des objets dans IndexController
 *
 * Fournit au controller les paramètres de connexion et les classes d'appel et de traitement des requêtes.
 *
 * @project sbm
 * @package SbmEsendex/src/Controller/Service
 * @filesource IndexControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Controller\Service;

use SbmEsendex\Controller\IndexController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmEsendex\Model\ApiSms;

class IndexControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'api_sms' => $sm->get(ApiSms::class)
        ];
        return new IndexController($config_controller);
    }
}