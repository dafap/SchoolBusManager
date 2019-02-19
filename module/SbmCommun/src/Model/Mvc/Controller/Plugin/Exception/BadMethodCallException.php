<?php
/**
 * Exception déclenchée par une classe du namespace SbmCommun\Model\Mvc\Controller\Plugin
 * 
 * Exception émise si une méthode de rappel n'existe pas ou si certains de ses arguments sont manquants.
 *
 * @project sbm
 * @package SbmCommun/Model/mvc/Controller/Plugin/Exception
 * @filesource BadMethodCallException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Mvc\Controller\Plugin\Exception;

class BadMethodCallException extends \BadMethodCallException implements ExceptionInterface
{
}