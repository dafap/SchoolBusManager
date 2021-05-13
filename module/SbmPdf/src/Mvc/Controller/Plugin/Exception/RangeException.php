<?php
/**
 * Exception déclenchée par une classe du namespace SbmPdf\Mvc\Controller\Plugin
 *
 * Exception émise pour indiquer des erreurs d'intervalle lors de l'exécution du
 * programme.
 * Normalement, cela signifie qu'il y a une erreur arithmétique autre qu'un débordement.
 * C'est l'équivalent en cours d'exécution de DomainException.
 *
 * @project sbm
 * @package SbmPdf/src/Mvc/Controller/Plugin/Exception
 * @filesource RangeException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 fév. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Mvc\Controller\Plugin\Exception;

class RangeException extends \RangeException implements ExceptionInterface
{
}