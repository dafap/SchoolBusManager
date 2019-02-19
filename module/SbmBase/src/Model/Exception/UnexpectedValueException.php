<?php
/**
 * Exception déclenchée par une classe du namespace SbmBase\Model
 * 
 * Exception émise si une valeur ne fait pas partie d'une liste de valeurs. 
 * Typiquement, elle survient lorsqu'une fonction appelle une autre fonction 
 * et attend que la valeur retournée soit d'un certain type ou d'une certaine valeur, 
 * sans inclure les erreurs relatives à l'arithmétique ou au buffer.
 *
 * @project sbm
 * @package SbmBase/Model/Exception
 * @filesource UnexpectedValueException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmBase\Model\Exception;

class UnexpectedValueException extends \UnexpectedValueException
{
}