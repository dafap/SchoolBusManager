<?php
/**
 * Exception déclenchée par les classes du dossier SbmAuthentification/Authentication
 * 
 * Exception émise si un argument ne correspond pas au type attendu
 *
 * @project sbm
 * @package SbmAuthentification/Authentication/Exception
 * @filesource InvalidArgumentException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmAuthentification\Authentication\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements 
    ExceptionInterface
{
} 