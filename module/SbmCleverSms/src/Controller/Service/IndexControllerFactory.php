<?php
/**
 * Injection des objets dans IndexController
 *
 * Fournit au controller les paramètres de connexion et les classes d'appel et de traitement des requêtes.
 *
 * @project sbm
 * @package SbmCleverSms/src/Controller/Service
 * @filesource IndexControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCleverSms\Controller\Service;

use SbmCleverSms\Controller\IndexController;
use SbmCleverSms\Model\CurlRequest;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndexControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'curl_request' => $sm->get(CurlRequest::class)
        ];
        return new IndexController($config_controller);
    }
}