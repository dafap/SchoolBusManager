<?php
/**
 * Interface pour  accéder aux classes de la librairie tcpdf
 * Function callback pour spl_autoload_register()
 *
 *
 * @project dafap/DafapTcPdf
 * @package dafap/DafapTcPdf
 * @filesource autoload_function.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mai 2014
 * @version 2014-1
 */

return function ($class) {
    static $map;
    if (!$map) $map = include __DIR__ . '/autoload_classmap.php';
    if (!isset($map[$class])) return false;

    return include $map[$class];
};