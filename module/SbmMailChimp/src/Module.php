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
 * @date 15 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmMailChimp;

use SbmBase\Model\StdLib;
use SbmBase\Module\AbstractModule;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module extends AbstractModule implements AutoloaderProviderInterface
{

    /**
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    'DrewM\MailChimp' => realpath(
                        StdLib::findParentPath(__DIR__, 'vendor/drewm/mailchimp-api/src'))
                ]
            ]
        ];
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