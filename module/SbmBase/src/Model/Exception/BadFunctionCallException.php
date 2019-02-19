<?php
/**
 * Exception déclenchée par une classe du namespace SbmBase\Model
 * 
 * Exception émise si une fonction de rappel n'existe pas ou si certains de ses arguments sont manquants.
 *
 * @project sbm
 * @package SbmBase/Model/Exception
 * @filesource BadFunctionCallException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmBase\Model\Exception;

class BadFunctionCallException extends \BadFunctionCallException
{
}