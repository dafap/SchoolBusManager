<?php
/**
 * Exception déclenchée par une classe du namespace SbmCommun\Model ou d'un namespace dérivé
 *
 * Exception émise quand une erreur est rencontrée durant l'exécution.
 *
 * @project sbm
 * @package SbmCommun/Arlysere
 * @filesource RuntimeException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere\Exception;

class RuntimeException extends \RuntimeException implements ExceptionInterface
{
}