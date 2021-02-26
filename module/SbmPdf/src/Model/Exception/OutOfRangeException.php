<?php
/**
 * Exception déclenchée par une classe du namespace SbmPdf\Model
 *
 * Exception émise lorsqu'un index illégal est demandé.
 * Elle représente les erreurs qui devraient être détectées au moment de la compilation.
 *
 * @project sbm
 * @package SbmPdf/src/Model/Exception
 * @filesource OutOfRangeException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 déc. 2020
 * @version 2020-2.6.1
 */
namespace SbmPdf\Model\Exception;

class OutOfRangeException extends \OutOfRangeException implements ExceptionInterface
{
}