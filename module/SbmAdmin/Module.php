<?php
/**
 * Module SbmAdmin pour l'administration de l'application
 * - paramétrages
 * - données générales
 * - structures d'exportation et d'importation
 *
 * @project sbm
 * @package module/SbmAdmin
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 janv. 2014
 * @version 2014-1
 */
namespace SbmAdmin;

use ZfcBase\Module\AbstractModule;

class Module extends AbstractModule
{

    public function getDir()
    {
        return __DIR__;
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }
}
