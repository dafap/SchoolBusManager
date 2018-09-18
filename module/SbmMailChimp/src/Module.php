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
 * @date 18 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmMailChimp;

use SbmBase\Module\AbstractModule;
use SbmBase\Model\StdLib;

class Module extends AbstractModule
{

    /**
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        $autoload = array_merge_recursive(parent::getAutoloaderConfig(),
            [
                'Zend\Loader\StandardAutoloader' => [
                    'namespaces' => [
                        'DrewM\MailChimp' => realpath(
                            StdLib::findParentPath(__DIR__,
                                'vendor/drewm/mailchimp-api/src'))
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