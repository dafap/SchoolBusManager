<?php
/**
 * Exception déclenchée par une classe du namespace SbmCommun\Model\Db\ObjectData
 * 
 * Exception émise si un argument ne correspond pas au type attendu.
 *
 * @project sbm
 * @package SbmCommun/Model/Db/ObjectData/Exception
 * @filesource InvalidArgumentException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\ObjectData\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements 
    ExceptionInterface
{
}