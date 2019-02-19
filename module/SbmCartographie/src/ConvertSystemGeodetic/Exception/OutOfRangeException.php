<?php
/**
 * Exception déclenchée par une classe des namespaces SbmCartographie\ConvertSystemGeodetic\...
 * 
 * Exception émise lorsqu'un index illégal est demandé. 
 * Elle représente les erreurs qui devraient être détectées au moment de la compilation.
 *
 * @project sbm
 * @package SbmCartographie/ConvertSystemGeodetic/Exception
 * @filesource OutOfRangeException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmCartographie\ConvertSystemGeodetic\Exception;

class OutOfRangeException extends \OutOfRangeException implements ExceptionInterface
{
}