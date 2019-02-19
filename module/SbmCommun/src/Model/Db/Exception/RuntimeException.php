<?php
/**
 * Exception déclenchée par une classe du namespace SbmCommun\Model\Db ou dérivé
 * 
 * Exception émise quand une erreur est rencontrée durant l'exécution.
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Exception
 * @filesource RuntimeException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Exception;

class RuntimeException extends \RuntimeException implements ExceptionInterface
{
}