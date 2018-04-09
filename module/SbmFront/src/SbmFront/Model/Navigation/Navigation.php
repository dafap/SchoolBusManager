<?php
/**
 * Test menu dynamique
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmFront/Model/Navigation
 * @filesource Navigation.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmFront\Model\Navigation;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Service\DefaultNavigationFactory;

class Navigation extends DefaultNavigationFactory
{

    protected function getPages(ServiceLocatorInterface $serviceLocator)
    {
        if (null === $this->pages) {
            // $fetchMenu = $serviceLocator->get('menu')->fetchAll();
            $fetchMenu = [
                'pages' => []
            ];
            $configuration['navigation'][$this->getName()] = [];
            foreach ($fetchMenu as $key => $row) {
                // $subMenu = $serviceLocator->get('menu')->fetchAllSubMenus($row['id']);
                $subMenu = [];
                if (! empty($subMenu)) {
                    $pages = [];
                    foreach ($subMenu as $k => $v) {
                        foreach ($v as $field => $value) {
                            $page['label'] = $value['heading'];
                            $page['route'] = 'visas';
                            if ($value['path'] == $row['path']) {
                                $page['params'] = [
                                    'action' => 'index',
                                    'category' => $this->$row['path']
                                ];
                            }
                            // $subCatMenu = $serviceLocator->get('menu')->fetchAllSubCatMenus($value['id']);
                            $subCatMenu = [];
                            $subcatpages = [];
                            $subcatgroup = [];
                            $group = [];
                            if (count($subCatMenu) > 0) {
                                foreach ($subCatMenu as $k => $v) {
                                    foreach ($v as $field => $value1) {
                                        $subpage['label'] = $value1['heading'];
                                        $subpage['route'] = 'visas';
                                        if ($value['path'] == $row['path']) {
                                            $subpage['params'] = [
                                                'action' => 'index',
                                                'category' => $row['path'],
                                                'sub_category' => $value1['path']
                                            ];
                                        } elseif ($row['id'] == 76) {
                                            $subpage['params'] = [
                                                'action' => 'index',
                                                'category' => $value['path'],
                                                'sub_category' => $value1['path']
                                            ];
                                        } else {
                                            $subpage['params'] = [
                                                'action' => 'index',
                                                'category' => $row['path'],
                                                'sub_category' => $value['path'],
                                                'id' => $value1['path']
                                            ];
                                        }
                                    }
                                    $group[] = $subpage;
                                }
                                $page['pages'] = $group;
                                $pages[] = $page;
                            }
                        }
                    }
                }
                $configuration['navigation'][$this->getName()][$row['name']] = [
                    'label' => $row['name'],
                    'route' => 'visas',
                    'params' => [
                        'action' => 'index',
                        'category' => $row['path']
                    ],
                    'pages' => $pages
                ];
            }
            if (! isset($configuration['navigation'])) {
                throw new Exception\InvalidArgumentException(
                    'Could not find navigation configuration key');
            }
            if (! isset($configuration['navigation'][$this->getName()])) {
                throw new Exception\InvalidArgumentException(
                    sprintf('Failed to find a navigation container by the name "%s"', 
                        $this->getName()));
            }
            $application = $serviceLocator->get('Application');
            $routeMatch = $application->getMvcEvent()->getRouteMatch();
            $router = $application->getMvcEvent()->getRouter();
            $pages = $this->getPagesFromConfig(
                $configuration['navigation'][$this->getName()]);
            $this->pages = $this->injectComponents($pages, $routeMatch, $router);
        }
        return $this->pages;
    }
}