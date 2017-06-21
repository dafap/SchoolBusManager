<?php
/**
 * Extension de l'élément Zend\Form\Time pour le format 'H:i'
 *
 * Nécessaire suite à la mise à jour du framework le 19/06/2017
 * 
 * @project sbm
 * @package SbmCommun\Form\Element
 * @filesource Time.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 juin 2017
 * @version 2017-3.3.4
 */
namespace SbmCommun\Form\Element;
 
use Zend\Form\Element\Time as ZendFormElementTime;

class Time extends ZendFormElementTime
{
    const DATETIME_FORMAT = 'H:i';
}