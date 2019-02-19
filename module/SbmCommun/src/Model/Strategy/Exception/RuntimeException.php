<?php
/**
 * Exception déclenchée par une classe du namespace SbmCommun\Model\Strategy ou dérivé
 * 
 * Exception émise quand une erreur est rencontrée durant l'exécution.
 *
 * @project sbm
 * @package SbmCommun/Model/Strategy/Exception
 * @filesource RuntimeException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Strategy\Exception;

class RuntimeException extends \RuntimeException implements ExceptionInterface
{
}