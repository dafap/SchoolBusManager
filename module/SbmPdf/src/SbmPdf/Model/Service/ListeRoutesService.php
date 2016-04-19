<?php
/**
 * Liste des routes
 * 
 * Renvoie un tableau de valueOptions pour le Select du formulaire DocAffectation
 *
 * @project sbm
 * @package SbmPdf/Model/Service
 * @filesource ListeRoutesService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 avr. 2016
 * @version 2016-2
 */
namespace SbmPdf\Model\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Filter\Word\CamelCaseToDash;
use SbmPdf\Service\PdfManager;
use SbmPdf\Model\Exception;

class ListeRoutesService implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $pdfManager)
    {
        if (!($pdfManager instanceof PdfManager)) {
            $message = 'PdfManager attendu. On a reçu un %s.';
            throw new Exception(sprintf($message, gettype($pdfManager)));
        }
        // liste des controleurs associée à une route
        // $routes = array();
        $controllers = array();
        foreach ($pdfManager->get('routes') as $routeName => $description) {
            $routeUrl = $description['options']['route'];
            if ($description['type'] == 'segment') {
                $routeUrl = preg_replace('/(\[.*\])/', '', $routeUrl);
            }
            // $routes[$routeName] = array($routeUrl => $description['options']['defaults']['controller']);
            $controllers[$description['options']['defaults']['controller']] = $routeUrl;
            if (array_key_exists('child_routes', $description)) {
                foreach ($description['child_routes'] as $subRouteName => $detail) {
                    $routeUrl2 = $routeUrl . $detail['options']['route'];
                    if ($detail['type'] == 'segment') {
                        $routeUrl2 = preg_replace('/(\[.*\])/', '', $routeUrl2);
                    }
                    // $routes[$routeName . '/' . $subRouteName] = array($routeUrl2 => $detail['options']['defaults']['controller']);
                    $controllers[$detail['options']['defaults']['controller']] = $routeUrl2;
                }
            }
        }
        // liste des controleurs par module
        $valueOptions = array();
        $actions = array();
        $filter = new CamelCaseToDash();
        foreach ($pdfManager->get('controllers') as $alias => $classControler) {
            $methodes = get_class_methods($classControler);
            asort($methodes);
            foreach ($methodes as $key => &$item) {
                if (substr($item, - 6) == 'Action' && $item != 'notFoundAction' && $item != 'getMethodFromAction') {
                    //$method = $classControler . '::' . $item;
                    $item = strtolower($filter->filter(substr($item, 0, - 6)));
                    $method = $controllers[$alias] . '/' . $item;
                    if (array_key_exists($alias, $valueOptions)) {
                        $valueOptions[$alias]['options'][$method] = $item;
                    } else {
                        $valueOptions[$alias] = array(
                            'label' => $controllers[$alias],
                            'options' => array(
                                $method => $item
                            )
                        );
                    }
                }
            }
        }
        ksort($valueOptions);
        return $valueOptions;
    }
} 