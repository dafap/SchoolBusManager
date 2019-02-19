<?php
/**
 * Exception déclenchée par une classe du namespace SbmBase\Model
 * 
 * Exception qui représente les erreurs dans la logique du programme. 
 * Ce type d'exceptions doit obligatoirement faire l'objet d'une correction du code.
 *
 * @project sbm
 * @package SbmBase/Model/Exception
 * @filesource LogicException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmBase\Model\Exception;

class LogicException extends \LogicException implements ExceptionInterface
{
}