<?php
/**
 * Exception lancée par une classe du namespace
 *
 * @project sbm
 * @package SbmCartographie/GoogleMaps/Exception
 * @filesource InvalidArgumentException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmCartographie\GoogleMaps\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements 
    ExceptionInterface
{
} 