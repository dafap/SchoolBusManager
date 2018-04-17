<?php
/**
 * Injection des objets dans IndexController
 *
 * Préparation pour compatibilité avec ZF3
 * 
 * @project sbm
 * @package SbmFront/Controller/Service
 * @filesource IndexControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmFront\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmFront\Controller\IndexController;
use SbmFront\Form\Login;
use SbmBase\Model\StdLib;

class IndexControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $db_manager = $sm->get('Sbm\DbManager');
        $tCommunes = $db_manager->get('Sbm\Db\Table\Communes');
        $rows = $tCommunes->fetchAll(['membre' => 1], 'nom');
        $aCommunes = [];
        foreach ($rows as $c) {
            $aCommunes[] = $c->nom;
        }
        $config_controller = [
            'db_manager' => $db_manager,
            'communes_membres' => $aCommunes,
            'login_form' => $sm->get(Login::class),
            'client' => StdLib::getParamR(
                [
                    'sbm',
                    'client'
                ], $config_application),
            'accueil' => StdLib::getParamR(
                [
                    'sbm',
                    'layout',
                    'accueil'
                ], $config_application),
             'url_ts_region' => StdLib::getParamR(
                [
                    'sbm',
                    'ts-region'
                ], $config_application)
       ];        
        return new IndexController($config_controller);
    }
}