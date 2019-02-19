<?php
/**
 * Objet de gestion des enregistrements de la table système `calendar`
 *
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmCommun\Model\Db\ObjectData\Sys
 * @filesource Calendar.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\ObjectData\Sys;

use SbmCommun\Model\Db\ObjectData\AbstractObjectData;

class Calendar extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('calendarId');
    }
} 