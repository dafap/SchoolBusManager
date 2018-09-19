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
 * @date 19 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Model\Db\ObjectData;

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