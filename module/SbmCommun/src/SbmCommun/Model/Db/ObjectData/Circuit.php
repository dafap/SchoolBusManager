<?php
/**
 * Objet contenant les données à manipuler pour la table Circuits
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource Circuit.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\ObjectData;

class Circuit extends AbstractObjectData
{
    public function __construct() 
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('circuitId');
    }
}