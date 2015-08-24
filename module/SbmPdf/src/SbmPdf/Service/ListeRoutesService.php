<?php
/**
 * Liste des routes
 * 
 * Renvoie un tableau de valueOptions pour le Select du formulaire DocAffectation
 *
 * @project sbm
 * @package SbmPdf/Service
 * @filesource ListeRoutesService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 août 2015
 * @version 2015-1
 */
namespace SbmPdf\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Filter\Word\CamelCaseToDash;

class ListeRoutesService implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $sm)
    {
        // liste des controleurs associée à une route
        // $routes = array();
        $controllers = array();
        foreach ($sm->get('config')['router']['routes'] as $routeName => $description) {
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
        $listeControlers = $sm->get('config')['controllers']['invokables'];
        asort($listeControlers);
        $filter = new CamelCaseToDash();
        foreach ($listeControlers as $alias => $classControler) {
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