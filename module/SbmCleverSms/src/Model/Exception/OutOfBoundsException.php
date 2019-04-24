<?php
/**
 * Exception déclenchée par une classe du namespace SbmCleverSms\Model ou d'un namespace dérivé
 *
 * Exception émise quand une valeur n'est pas une clé valide.
 * Elle représente les erreurs qui ne peuvent pas être détectées au moment de la compilation.
 *
 * @project sbm
 * @package SbmCleverSms/Model/Exception
 * @filesource OutOfBoundsException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCleverSms\Model\Exception;

class OutOfBoundsException extends \OutOfBoundsException implements ExceptionInterface
{
}