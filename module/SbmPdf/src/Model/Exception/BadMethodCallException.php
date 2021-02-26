<?php
/**
 * Exception déclenchée par une classe du namespace SbmPdf\Model
 *
 * Exception émise si une méthode de rappel n'existe pas ou si certains de ses arguments
 * sont manquants.
 *
 * @project sbm
 * @package SbmPdf/src/Model/Exception
 * @filesource BadMethodCallException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 déc. 2020
 * @version 2020-2.6.1
 */
namespace SbmPdf\Model\Exception;

class BadMethodCallException extends \BadMethodCallException implements
    ExceptionInterface
{
}