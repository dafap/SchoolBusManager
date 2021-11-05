<?php
/**
 * Injection des objets dans OrganisateurController
 *
 * @project sbm
 * @package SbmPortail/src/Controller/Service
 * @filesource OrganisateurControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 oct. 2021
 * @version 2021-2.6.4
 */
namespace SbmPortail\Controller\Service;

use SbmBase\Model\StdLib;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use SbmPortail\Controller\OrganisateurController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OrganisateurControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $cm = $sm->get('Sbm\CartographieManager');
        $cartographie = $cm->get('cartographie');
        $projection = str_replace('ProjectionInterface',
            StdLib::getParam('system', $cartographie), ProjectionInterface::class);
        $nzone = StdLib::getParam('nzone', $cartographie, 0);
        $config_cartes = $cm->get('cartes');
        $google_api = $cm->get('google_api_browser');
        $auth = $sm->get('SbmAuthentification\Authentication')->by('email');
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'projection' => new $projection($nzone),
            'config_cartes' => $config_cartes,
            'url_api' => $google_api['js'],
            'categorieId' => $auth->getCategorieId(),
            'userId' => $auth->getUserId()
        ];
        return new OrganisateurController($config_controller);
    }
}