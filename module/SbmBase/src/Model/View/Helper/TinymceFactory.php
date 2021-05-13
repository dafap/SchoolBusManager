<?php
/**
 * Factory pour Tinymce
 *
 * Initialise l'URL d'accès à la librairie
 *
 * @project sbm
 * @package SbmBase/Model/View/Helper
 * @filesource TinymceFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmBase\Model\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmBase\Model\StdLib;

class TinymceFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $viewhelperManager)
    {
        $serviceLocator = $viewhelperManager->getServiceLocator();
        $tinymce_config = StdLib::getParam('tinymce', $serviceLocator->get('config'));
        return new Tinymce(StdLib::getParam('url', $tinymce_config),
            StdLib::getParam('attrs', $tinymce_config));
    }
}