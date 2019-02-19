<?php
/**
 * Exception déclenchée par une classe du namespace SbmBase\Model
 * 
 * Exception émise quand une erreur est rencontrée durant l'exécution.
 *
 * @project sbm
 * @package SbmBase/Model/Exception
 * @filesource RuntimeException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmBase\Model\Exception;

class RuntimeException extends \RuntimeException implements ExceptionInterface
{
}