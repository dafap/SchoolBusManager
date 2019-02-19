<?php
/**
 * Exception déclenchée par une classe du namespace SbmBase\Model
 * 
 * Exception émise lorsqu'une opération invalide est effectuée sur 
 * un conteneur vide, tel qu'une suppression d'élément.
 *
 * @project sbm
 * @package SbmBase/Model/Exception
 * @filesource UnderflowException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmBase\Model\Exception;

class UnderflowException extends \UnderflowException
{
}