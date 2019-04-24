<?php
/**
 * Objet contenant les données à manipuler pour la table Lots
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/Model/Db/ObjectData
 * @filesource Lot.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\ObjectData;

class Lot extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('lotId');
    }
}