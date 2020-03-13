<?php
/**
 * Exception déclenchée par une classe du namespace SbmCommun\Arlysere (ou dérivé)
 *
 * Exception émise si un argument ne correspond pas au type attendu.
 *
 * @project sbm
 * @package SbmBase/Arlysere/Exception
 * @filesource InvalidArgumentException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements
    ExceptionInterface
{
}