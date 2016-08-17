<?php
/**
 * Gestion des listes de diffusion par l'API de MailChimp, version 3
 *
 * Ce module utilise la bibliothèque `DrewM\MailChimp` et le module `SbmBase`
 * 
 * @project sbm
 * @package SbmMailChimp
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2016
 * @version 2016-2.2.0
 */
namespace SbmMailChimp;

use SbmBase\Module\AbstractModule;

class Module extends AbstractModule
{

    /**
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        $autoload = array_merge_recursive(parent::getAutoloaderConfig(), [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    'DrewM\MailChimp' => realpath(__DIR__ . '/../../vendor/drewm/mailchimp-api/src')
                ]
            ]
        ]);
        return $autoload;
    }

    public function getDir()
    {
        return __DIR__;
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }
}