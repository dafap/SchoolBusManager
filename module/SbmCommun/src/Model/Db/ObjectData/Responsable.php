<?php
/**
 * Objet contenant les données à manipuler pour la table Responsables
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource Responsable.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\ObjectData;

class Responsable extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('responsableId');
    }

    public function accepteSms()
    {
        $ok = ! empty($this->telephoneF) && $this->smsF == 1;
        $ok |= ! empty($this->telephoneP) && $this->smsP == 1;
        $ok |= ! empty($this->telephoneT) && $this->smsT == 1;
        return $ok;
    }
}