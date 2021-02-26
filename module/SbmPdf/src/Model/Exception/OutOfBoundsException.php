<?php
/**
 * Exception déclenchée par une classe du namespace SbmPdf\Model
 *
 * Exception émise quand une valeur n'est pas une clé valide.
 * Elle représente les erreurs qui ne peuvent pas être détectées au moment de la
 * compilation.
 *
 * @project sbm
 * @package SbmPdf/src/Model/Exception
 * @filesource OutOfBoundsException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 déc. 2020
 * @version 2020-2.6.1
 */
namespace SbmPdf\Model\Exception;

class OutOfBoundsException extends \OutOfBoundsException implements ExceptionInterface
{
}