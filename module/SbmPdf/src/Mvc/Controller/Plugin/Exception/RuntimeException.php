<?php
/**
 * Exception déclenchée par une classe du namespace SbmPdf\Mvc\Controller\Plugin
 *
 * Exception émise quand une erreur est rencontrée durant l'exécution.
 *
 * @project sbm
 * @package SbmPdf/src/Mvc/Controller/Plugin/Exception
 * @filesource RuntimeException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 fév. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Mvc\Controller\Plugin\Exception;

class RuntimeException extends \RuntimeException implements ExceptionInterface
{
}