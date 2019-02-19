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
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Form\Element;

use Zend\Form\Element\Time as ZendFormElementTime;

class Time extends ZendFormElementTime
{

    const DATETIME_FORMAT = 'H:i';
}