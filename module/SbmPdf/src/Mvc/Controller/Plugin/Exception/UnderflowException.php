<?php
/**
 * Exception déclenchée par une classe du namespace SbmPdf\Mvc\Controller\Plugin
 *
 * Exception émise lorsqu'une opération invalide est effectuée sur
 * un conteneur vide, tel qu'une suppression d'élément.
 *
 * @project sbm
 * @package SbmPdf/src/Mvc/Controller/Plugin/Exception
 * @filesource UnderflowException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 fév. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Mvc\Controller\Plugin\Exception;

class UnderflowException extends \UnderflowException implements ExceptionInterface
{
}