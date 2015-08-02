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
 * @date 1 août 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\ObjectData;

use SbmCommun\Model\Validator\CodeEtablissement;
use SbmCommun\Model\Validator\CodeService;

class UserEtablissement extends AbstractObjectData
{
    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName(array('userId', 'etablissementId'));
    }
}
 