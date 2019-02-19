<?php
/**
 * Injection des objets dans IndexController
 *
 * Préparation pour compatibilité avec ZF3
 * 
 * @project sbm
 * @package SbmMailChimp/Controller/Service
 * @filesource IndexControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmMailChimp\Controller\Service;

use SbmBase\Model\StdLib;
use SbmMailChimp\Controller\IndexController;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\GenericResource;
use Zend\Permissions\Acl\Role\GenericRole;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndexControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $authenticate = $sm->get('SbmAuthentification\Authentication');
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'client' => StdLib::getParamR([
                'sbm',
                'client'
            ], $config_application),
            'mail_config' => StdLib::getParamR([
                'sbm',
                'mail'
            ], $config_application),
            'authenticate' => $authenticate,
            'acl' => $this->createAcl($config_application['acl']),
            'mailchimp_key' => StdLib::getParamR([
                'sbm',
                'mailchimp',
                'key'
            ], $config_application, '')
        ];
        return new IndexController($config_controller);
    }

    private function createAcl($config_acl)
    {
        $acl = new Acl();
        // Ici, les rôles seront les categorieIds
        $categorieIds = array_merge([
            'guest' => 0
        ], array_flip($config_acl['roleId']));
        foreach ($config_acl['roles'] as $role => $parent) {
            if (is_null($parent)) {
                $acl->addRole(new GenericRole($categorieIds[$role]));
            } else {
                $acl->addRole(new GenericRole($categorieIds[$role]),
                    $categorieIds[$parent]);
            }
        }
        // les resources
        $resourcesMC = $config_acl['resources']['sbmmailchimp'];
        $acl->addResource(new GenericResource('sbmmailchimp'));
        foreach (StdLib::getParamR([
            'allow',
            'roles'
        ], $resourcesMC, []) as $role) {
            $acl->allow($categorieIds[$role], 'sbmmailchimp');
        }
        foreach (StdLib::getParam('actions', $resourcesMC) as $action => $description) {
            $acl->addResource(new GenericResource($action), 'sbmmailchimp');
            foreach (stdlib::getParamR([
                'allow',
                'roles'
            ], $description, []) as $role) {
                $acl->allow($categorieIds[$role], $action);
            }
            foreach (stdlib::getParamR([
                'deny',
                'roles'
            ], $description, []) as $role) {
                $acl->deny($categorieIds[$role], $action);
            }
        }
        return $acl;
    }
}
 