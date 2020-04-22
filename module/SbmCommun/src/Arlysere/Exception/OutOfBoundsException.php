<?php
/**
 * Exception déclenchée par une classe du namespace SbmCommun\Model ou d'un namespace dérivé
 *
 * Exception émise quand une valeur n'est pas une clé valide.
 * Elle représente les erreurs qui ne peuvent pas être détectées au moment de la compilation.
 *
 * @project sbm
 * @package SbmCommun/Arlysere
 * @filesource OutOfBoundsException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere\Exception;

class OutOfBoundsException extends \OutOfBoundsException implements ExceptionInterface
{
}