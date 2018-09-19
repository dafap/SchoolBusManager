<?php
/**
 * Objet contenant les données à manipuler pour la table `users-etablissements`
 *
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource UserEtablissement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Model\Db\ObjectData;

class UserEtablissement extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName([
            'userId',
            'etablissementId'
        ]);
    }
}
 