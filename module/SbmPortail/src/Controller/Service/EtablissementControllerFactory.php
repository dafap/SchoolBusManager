<?php
/**
 * Injection des objets dans EtablissementController
 *
 * @project sbm
 * @package SbmPortail/src/Controller/Service
 * @filesource EtablissementControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 sept. 2020
 * @version 2020-2.6.1
 */
namespace SbmPortail\Controller\Service;

use SbmAuthentification\Model\CategoriesInterface;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use SbmPortail\Controller\EtablissementController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EtablissementControllerFactory implements FactoryInterface
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
        $enTantQue = Session::get('etablissement', false, 'enTantQue');
        if ($enTantQue !== false &&
            ($categorieId == CategoriesInterface::SECRETARIAT_ID ||
                $categorieId == CategoriesInterface::GESTION_ID ||
                $categorieId == CategoriesInterface::ADMINISTRATEUR_ID ||
                $categorieId == CategoriesInterface::SUPER_ADMINISTRATEUR_ID)) {
                    $categorieId = CategoriesInterface::ETABLISSEMENT_ID;
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
        return new EtablissementController($config_controller);
    }
}