<?php
/**
 * Objet contenant les données à manipuler pour la table `users-organismes`
 *
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/Model/Db/ObjectData
 * @filesource UserOrganisme.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\ObjectData;

class UserOrganisme extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName([
            'userId',
            'organismeId'
        ]);
    }
}