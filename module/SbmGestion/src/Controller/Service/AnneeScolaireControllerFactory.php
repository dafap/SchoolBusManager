<?php
/**
 * Injection des objets dans AnneeScolaireController
 *
 * Préparation pour compatibilité avec ZF3
 * 
 * @project sbm
 * @package SbmGestion/Controller/Service
 * @filesource AnneeScolaireControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmGestion\Controller\Service;

use SbmBase\Model\StdLib;
use SbmGestion\Controller\AnneeScolaireController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AnneeScolaireControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'cartographie_manager' => $sm->get('Sbm\CartographieManager'),
            'authenticate' => $sm->get('SbmAuthentification\Authentication'),
            'mail_config' => StdLib::getParamR([
                'sbm',
                'mail'
            ], $config_application),
            'paginator_count_per_page' => StdLib::getParamR(
                [
                    'paginator',
                    'count_per_page'
                ], $config_application)
        ];
        return new AnneeScolaireController($config_controller);
    }
}