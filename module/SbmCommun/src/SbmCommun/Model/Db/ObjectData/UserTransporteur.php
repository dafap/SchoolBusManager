<?php
/**
 * Objet contenant les données à manipuler pour la table `users-transporteurs`
 *
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource UserTransporteur.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\ObjectData;

use SbmCommun\Model\Validator\CodeEtablissement;
use SbmCommun\Model\Validator\CodeService;

class UserTransporteur extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName([
            'userId',
            'transporteurId'
        ]);
    }
}