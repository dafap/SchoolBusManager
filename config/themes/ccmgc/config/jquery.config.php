<?php
/**
 * Fichier de configuration
 *
 * Thème CCMGC
 *
 * @project sbm
 * @package config/themes/ccmgc/config
 * @filesource jquery.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 04/05/2019
 * @version 2019-2.5.0
 */

return array (
  'jquery' => 
  array (
    'mode' => 'prepend',
    'js' => 
    array (
      0 => 'js/jquery-1.12.4/jquery.min.js',
    ),
  ),
  'jquery-ui' => 
  array (
    'mode' => 'append',
    'css' => 
    array (
      0 => 'js/jquery-ui-1.12.1.custom/jquery-ui.min.css',
    ),
    'js' => 
    array (
      0 => 'js/jquery-ui-1.12.1.custom/jquery-ui.min.js',
    ),
  ),
  'jquery.formatCurrency' => 
  array (
    'mode' => 'append',
    'js' => 
    array (
      0 => 'js/jquery.formatCurrency-1.4.0.i18n/jquery.formatCurrency-1.4.0.min.js',
    ),
  ),
  'jquery-cookie' => 
  array (
    'mode' => 'append',
    'js' => 
    array (
      0 => 'js/jquery-cookie.master/jquery.cookie.js',
    ),
  ),
  'jquery-migrate' => 
  array (
    'mode' => 'append',
    'js' => 
    array (
      0 => 'js/jquery-migrate-1.4.1.js',
    ),
  ),
);