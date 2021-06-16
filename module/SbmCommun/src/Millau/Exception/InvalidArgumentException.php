<?php
/**
 * Exception déclenchée par une classe du namespace SbmCommun\Millau (ou dérivé)
 *
 * Exception émise si un argument ne correspond pas au type attendu.
 *
 * @project sbm
 * @package SbmCommun/src/Millau/Exception
 * @filesource InvalidArgumentException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 juin 2021
 * @version 2021-2.5.11
 */
namespace SbmCommun\Millau\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements
ExceptionInterface
{
}