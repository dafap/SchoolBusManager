<?php
/**
 * Injection des objets dans CarteController
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmCartographie/Controller/Service
 * @filesource CarteControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 avr. 2016
 * @version 2016-2
 */
namespace SbmCartographie\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use SbmCartographie\Controller\CarteController;
use SbmCommun\Model\StdLib;

class CarteControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $cm = $sm->get('Sbm\CartographieManager');
        $cartographie = $cm->get('cartographie');
        $projection = str_replace('ProjectionInterface', StdLib::getParam('system', $cartographie), ProjectionInterface::class);
        $nzone = StdLib::getParam('nzone', $cartographie, 0);
        $config_cartes = $cm->get('cartes');
        
        return new CarteController([
            'db_manager' => $sm->get('Sbm\DbManager'),
            'projection' => new $projection($nzone),
            'config_cartes' => $config_cartes,
            'user' => $sm->get('Dafap\Authenticate')
                ->by()
                ->getIdentity()
        ]);
    }
}