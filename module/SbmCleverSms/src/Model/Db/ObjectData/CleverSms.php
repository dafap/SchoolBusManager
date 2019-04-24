<?php
/**
 * Objet contenant les données à manipuler pour la table CleverSms
 * (à déclarer dans module.config.php)
 *
 * @project sms
 * @package SbmCleverSms/src/Model/Db/ObjectData
 * @filesource CleverSms.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCleverSms\Model\Db\ObjectData;

use SbmCommun\Model\Db\ObjectData\AbstractObjectData;

class CleverSms extends AbstractObjectData
{
    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('cleversmsId');
    }
}