<?php
/**
 * Objet contenant les données à manipuler pour la table Transporteurs
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource Zonage.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 juil. 2019
 * @version 2019-2.5.1
 */
namespace SbmCommun\Model\Db\ObjectData;

class Zonage extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('zonageId');
    }
}