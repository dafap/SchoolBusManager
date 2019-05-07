<?php
/**
 * Objet contenant les données à manipuler pour la table du plugin
 * (déclarée dans /config/autoload/sbm.global.php)
 *
 * @project sbm
 * @package SbmPaiement/Plugin/PayFiP/Db/ObjectData
 * @filesource ObjectData.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmPaiement\Plugin\PayFiP\Db\ObjectData;

use SbmCommun\Model\Db\ObjectData\AbstractObjectData;

class ObjectData extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('payfipId');
    }
}