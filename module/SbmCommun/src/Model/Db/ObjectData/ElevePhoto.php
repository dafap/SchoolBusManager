<?php
/**
 * Objet contenant les données à manipuler pour la table ElevesPhotos
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/ObjectData
 * @filesource ElevePhoto.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\ObjectData;

class ElevePhoto extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('eleveId');
    }
}