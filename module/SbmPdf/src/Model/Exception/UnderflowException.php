<?php
/**
 * Exception déclenchée par une classe du namespace SbmPdf\Model
 *
 * Exception émise lorsqu'une opération invalide est effectuée sur
 * un conteneur vide, tel qu'une suppression d'élément.
 *
 * @project sbm
 * @package SbmPdf/src/Model/Exception
 * @filesource UnderflowException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 déc. 2020
 * @version 2020-2.6.1
 */
namespace SbmPdf\Model\Exception;

class UnderflowException extends \UnderflowException implements ExceptionInterface
{
}