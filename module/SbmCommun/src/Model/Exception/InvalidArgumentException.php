<?php
/**
 * Exception déclenchée par une classe du namespace SbmCommun\Model\Db (ou dérivé)
 *
 * Exception émise si un argument ne correspond pas au type attendu.
 *
 * @project sbm
 * @package SbmBase/Model/Exception
 * @filesource InvalidArgumentException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements
    ExceptionInterface
{
}