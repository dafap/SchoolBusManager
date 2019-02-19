<?php
/**
 * Exception déclenchée par une classe des namespaces SbmCartographie\ConvertSystemGeodetic\...
 * 
 * Exception lancée si une valeur n'adhère pas à un domaine de données défini et valide.
 *
 * @project sbm
 * @package SbmCartographie/ConvertSystemGeodetic/Exception
 * @filesource DomainException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmCartographie\ConvertSystemGeodetic\Exception;

class DomainException extends \DomainException implements ExceptionInterface
{
}