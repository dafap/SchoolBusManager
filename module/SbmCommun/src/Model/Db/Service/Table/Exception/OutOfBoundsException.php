<?php
/**
 * Exception déclenchée par une classe du namespace SbmCommun\Model\Db\Service\Table
 * 
 * Exception émise quand une erreur est rencontrée durant l'exécution.
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Table/Exception
 * @filesource OutOfBoundsException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table\Exception;

class OutOfBoundsException extends \OutOfBoundsException implements ExceptionInterface
{
}