<?php
/**
 * Objet contenant les données à manipuler pour la table Affectations
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource Affectation.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 mars 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\ObjectData;

class Affectation extends AbstractObjectData
{
    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName(array(
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'correspondance'
        ));
    }
}