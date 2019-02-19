<?php
/**
 * Exception déclenchée par une classe du namespace SbmCommun\Model\Strategy ou d'un namespace dérivé
 * 
 * Exception émise quand une valeur n'est pas une clé valide. 
 * Elle représente les erreurs qui ne peuvent pas être détectées au moment de la compilation.
 *
 * @project sbm
 * @package SbmCommun/Model/Strategy/Exception
 * @filesource OutOfBoundsException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Strategy\Exception;

class OutOfBoundsException extends \OutOfBoundsException implements ExceptionInterface
{
}