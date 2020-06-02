<?php
/**
 * Objet contenant les données à manipuler pour la table EsendexTelephones
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package
 * @filesource EsendexTelephone.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model\Db\objectData;

use SbmCommun\Model\Db\ObjectData\AbstractObjectData;

class EsendexTelephone extends AbstractObjectData
{
    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('esendextelephoneId');
    }
}