<?php
/**
 * Exception déclenchée par une classe du namespace SbmPdf\Model
 *
 * Exception lancée si une valeur n'adhère pas à un domaine de données défini et valide.
 *
 * @project sbm
 * @package SbmPdf/src/Model/Exception
 * @filesource DomainException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 déc. 2020
 * @version 2020-2.6.1
 */
namespace SbmPdf\Model\Exception;

class DomainException extends \DomainException implements ExceptionInterface
{
}