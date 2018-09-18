<?php
/**
 * Objet contenant les données à manipuler pour la table du plugin
 * (déclarée dans /config/autoload/sbm.global.php)
 *
 * @project sbm
 * @package SbmPaiement/Plugin/SystemPay/Db/ObjectData
 * @filesource ObjectData.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 avr. 2015
 * @version 2015-1
 */
namespace SbmPaiement\Plugin\SystemPay\Db\ObjectData;

use SbmCommun\Model\Db\ObjectData\AbstractObjectData;

class ObjectData extends AbstractObjectData
{
    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('systempayId');
    }
}