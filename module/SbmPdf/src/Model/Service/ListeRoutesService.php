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
 * @date 7 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmPdf\Model\Service;

use SbmPdf\Model\Exception;
use SbmPdf\Service\PdfManager;
use Zend\Filter\Word\CamelCaseToDash;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ListeRoutesService implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $pdfManager)
    {
        if (! ($pdfManager instanceof PdfManager)) {
            $message = 'PdfManager attendu. On a reçu un %s.';
            throw new Exception(sprintf($message, gettype($pdfManager)));
        }
        // liste des controleurs associée à une route
        // $routes = [];
        $controllers = [];
        foreach ($pdfManager->get('routes') as $description) {
            $routeUrl = $description['options']['route'];
            if ($description['type'] == 'segment') {
                $routeUrl = preg_replace('/(\[.*\])/', '', $routeUrl);
            }
            $controllers[$description['options']['defaults']['controller']] = $routeUrl;
            if (array_key_exists('child_routes', $description)) {
                foreach ($description['child_routes'] as $detail) {
                    $routeUrl2 = $routeUrl . $detail['options']['route'];
                    if ($detail['type'] == 'segment') {
                        $routeUrl2 = preg_replace('/(\[.*\])/', '', $routeUrl2);
                    }
                    $controllers[$detail['options']['defaults']['controller']] = $routeUrl2;
                }
            }
        }
        // liste des controleurs par module
        $valueOptions = [];
        $filter = new CamelCaseToDash();
        foreach ($pdfManager->get('controllers') as $alias => $classControler) {
            $methodes = (array) get_class_methods($alias);
            asort($methodes);
            foreach ($methodes as &$item) {
                if (substr($item, - 6) == 'Action' && $item != 'notFoundAction' &&
                    $item != 'getMethodFromAction') {
                    $item = strtolower($filter->filter(substr($item, 0, - 6)));
                    $method = $controllers[$alias] . '/' . $item;
                    if (array_key_exists($alias, $valueOptions)) {
                        $valueOptions[$alias]['options'][$method] = $item;
                    } else {
                        $valueOptions[$alias] = [
                            'label' => $controllers[$alias],
                            'options' => [
                                $method => $item
                            ]
                        ];
                    }
                }
            }
        }
        unset($classControler);
        ksort($valueOptions);
        return $valueOptions;
    }
} 