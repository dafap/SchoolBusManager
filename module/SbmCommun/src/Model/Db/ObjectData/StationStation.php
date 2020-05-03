<?php
/**
 * Objet contenant les données à manipuler pour la table StationStation
 * (à déclarer dans module.config.php)
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource StationStation.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\ObjectData;

class StationStation extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName([
            'station1Id',
            'station2Id'
        ]);
    }
}