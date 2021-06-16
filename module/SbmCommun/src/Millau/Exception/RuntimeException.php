<?php
/**
 * Exception déclenchée par une classe du namespace SbmCommun\Millau ou d'un namespace dérivé
 *
 * Exception émise quand une erreur est rencontrée durant l'exécution.
 *
 * @project sbm
 * @package SbmCommun/src/Millau/Exception
 * @filesource RuntimeException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 juin 2021
 * @version 2021-2.6.11
 */
namespace SbmCommun\Millau\Exception;

class RuntimeException extends \RuntimeException implements ExceptionInterface
{
}