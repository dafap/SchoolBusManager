<?php
/**
 * Exception lancée par une classe du namespace SbmCommun\Model\Db
 *
 * Exception lancée si une valeur n'adhère pas à un domaine de données défini et valide.
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Exception
 * @filesource DomainException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Exception;

class DomainException extends \DomainException implements ExceptionInterface
{
}