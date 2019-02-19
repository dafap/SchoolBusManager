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
 * @date 27 déc. 2018
 * @version 2018-2.4.6
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