<?php
/**
 * Exception déclenchée par une classe du namespace SbmBase\Model
 * 
 * Exception émise pour indiquer des erreurs d'intervalle lors de l'exécution du programme. 
 * Normalement, cela signifie qu'il y a une erreur arithmétique autre qu'un débordement. 
 * C'est l'équivalent en cours d'exécution de DomainException.
 *
 * @project sbm
 * @package SbmBase/Model/Exception
 * @filesource RangeException.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmBase\Model\Exception;

class RangeException extends \RangeException
{
}