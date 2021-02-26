<?php
/**
 * Exception déclenchée par une classe du namespace SbmPdf\Model
 *
 * Exception émise si une taille est invalide.
 *
 * @project sbm
 * @package SbmPdf/src/Model/Exception
 * @filesource LengthException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 déc. 2020
 * @version 2020-2.6.1
 */
namespace SbmPdf\Model\Exception;

class LengthException extends \LengthException implements ExceptionInterface
{
}