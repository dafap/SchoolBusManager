<?php
/**
 * Exception déclenchée par une classe du namespace SbmPdf\Mvc\Controller\Plugin
 *
 * Exception émise si une méthode de rappel n'existe pas ou si certains de ses arguments
 * sont manquants.
 *
 * @project sbm
 * @package SbmPdf/src/Mvc/Controller/Plugin/Exception
 * @filesource BadMethodCallException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 fév. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Mvc\Controller\Plugin\Exception;

class BadMethodCallException extends \BadMethodCallException implements
    ExceptionInterface
{
}