<?php
/**
 * Objet contenant les données à manipuler pour la table RpiEtablissement
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource SimulationEtablissement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 août 2018
 * @version 2018-2.4.3
 */
namespace SbmCommun\Model\Db\ObjectData;

class SimulationEtablissement extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('origineId');
    }
}