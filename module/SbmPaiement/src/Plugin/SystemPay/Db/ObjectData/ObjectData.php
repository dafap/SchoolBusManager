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
 * @date 5 avr. 2018
 * @version 2018-2.4.0
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