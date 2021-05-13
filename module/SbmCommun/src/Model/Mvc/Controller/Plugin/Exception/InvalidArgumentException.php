<?php
/**
 * Exception déclenchée par une classe du namespace SbmCommun\Model\Db (ou dérivé)
 *
 * Exception émise si un argument ne correspond pas au type attendu.
 *
 * @project sbm
 * @package SbmCommun/Model/Mvc/Controller/Plugin/Exception
 * @filesource InvalidArgumentException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 déc. 2020
 * @version 2020-2.6.1
 */
namespace SbmCommun\Model\Mvc\Controller\Plugin\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements
    ExceptionInterface
{
}