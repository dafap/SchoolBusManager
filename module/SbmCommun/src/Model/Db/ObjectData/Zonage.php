<?php
/**
 * Objet contenant les données à manipuler pour la table 'zonage'
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource Zonage.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 juin 2020
 * @version 2020-2.5.4
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