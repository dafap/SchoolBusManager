<?php
/**
 * Module SbmInstallation
 *
 * @project sbm
 * @package module/SbmInstallation
 * @filesource autoload_classmap.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 juin 2015
 * @version 2015-1
 */

return array(
    'SbmInstallation\Module'                             => __DIR__ . '/Module.php',
    'SbmInstallation\Controller\IndexController'         => __DIR__ . '/src/SbmInstallation/Controller/IndexController.php',
    'SbmInstallation\Form\DumpTables'                    => __DIR__ . '/src/SbmInstallation/Form/DumpTables.php',
    'SbmInstallation\Model\CreateTables'                 => __DIR__ . '/src/SbmInstallation/Model/CreateTables.php',
    'SbmInstallation\Model\DumpTables'                   => __DIR__ . '/src/SbmInstallation/Model/DumpTables.php',
    'SbmInstallation\Model\Exception'                    => __DIR__ . '/src/SbmInstallation/Model/Exception.php',
);