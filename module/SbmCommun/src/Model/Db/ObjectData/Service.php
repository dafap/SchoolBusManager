<?php
/**
 * Objet contenant les données à manipuler pour la table Services
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource Service.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\ObjectData;

class Service extends AbstractObjectData
{
    use \SbmCommun\Model\Traits\ServiceTrait;

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('serviceId');
    }

    public function designation()
    {
        return $this->identifiantService($this->getArrayCopy());
    }
}