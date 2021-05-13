<?php
/**
 * Injection des objets dans CommuneController
 *
 * @project sbm
 * @package SbmPortail/src/Controller/Service
 * @filesource CommuneControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 mars 2021
 * @version 2021-2.6.1
 */
namespace SbmPortail\Controller\Service;

use SbmAuthentification\Model\CategoriesInterface;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use SbmPortail\Controller\CommuneController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CommuneControllerFactory implements FactoryInterface
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
        $categorieId = $auth->getCategorieId();
        $userId = $auth->getUserId();
        $enTantQue = Session::get('commune', false, 'enTantQue');
        if ($enTantQue !== false &&
            ($categorieId == CategoriesInterface::SECRETARIAT_ID ||
            $categorieId == CategoriesInterface::GESTION_ID ||
            $categorieId == CategoriesInterface::ADMINISTRATEUR_ID ||
            $categorieId == CategoriesInterface::SUPER_ADMINISTRATEUR_ID)) {
            $categorieId = CategoriesInterface::COMMUNE_ID;
            $userId = $enTantQue;
        }
        $config_controller = [
            'RenderPdfService' => $sm->get('RenderPdfService'),
            'db_manager' => $sm->get('Sbm\DbManager'),
            'projection' => new $projection($nzone),
            'config_cartes' => $config_cartes,
            'url_api' => $google_api['js'],
            'categorieId' => $categorieId,
            'userId' => $userId
        ];
        return new CommuneController($config_controller);
    }
}