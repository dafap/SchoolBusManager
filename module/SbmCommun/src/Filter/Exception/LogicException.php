<?php
/**
 * Exception déclenchée par une classe du namespace SbmCommun\Filter
 * 
 * Exception qui représente les erreurs dans la logique du programme. 
 * Ce type d'exceptions doit obligatoirement faire l'objet d'une correction du code.
 *
 * @project sbm
 * @package SbmCommun/Filter/Exception
 * @filesource LogicException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Filter\Exception;

class LogicException extends \LogicException implements ExceptionInterface
{
}