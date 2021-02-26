<?php
/**
 * Exception déclenchée par une classe du namespace SbmPdf\Model
 *
 * Exception qui représente les erreurs dans la logique du programme.
 * Ce type d'exceptions doit obligatoirement faire l'objet d'une correction du code.
 *
 * @project sbm
 * @package SbmPdf/src/Model/Exception
 * @filesource LogicException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 déc. 2020
 * @version 2020-2.6.1
 */
namespace SbmPdf\Model\Exception;

class LogicException extends \LogicException implements ExceptionInterface
{
}