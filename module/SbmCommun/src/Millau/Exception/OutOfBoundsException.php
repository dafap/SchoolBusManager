<?php
/**
 * Exception déclenchée par une classe du namespace SbmCommun\Millau ou d'un namespace dérivé
 *
 * Exception émise quand une valeur n'est pas une clé valide.
 * Elle représente les erreurs qui ne peuvent pas être détectées au moment de la compilation.
 *
 * @project sbm
 * @package SbmCommun/src/Millau/Exception
 * @filesource OutOfBoundsException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 juin 2021
 * @version 2021-2.5.11
 */
namespace SbmCommun\Millau\Exception;

class OutOfBoundsException extends \OutOfBoundsException implements ExceptionInterface
{
}