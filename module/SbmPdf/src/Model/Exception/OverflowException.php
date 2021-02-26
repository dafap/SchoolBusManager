<?php
/**
 * Exception déclenchée par une classe du namespace SbmPdf\Model
 *
 * Exception émise lors de l'ajout d'un élément à un conteneur plein.
 *
 * @project sbm
 * @package SbmPdf/src/Model/Exception
 * @filesource OverflowException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 déc. 2020
 * @version 2020-2.6.1
 */
namespace SbmPdf\Model\Exception;

class OverflowException extends \OverflowException implements ExceptionInterface
{
}