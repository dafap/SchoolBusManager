<?php
/**
 * Objet contenant les données à manipuler pour la table EsendexBatches
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package
 * @filesource EsendexBatch.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model\Db\objectData;

use SbmCommun\Model\Db\ObjectData\AbstractObjectData;

class EsendexBatch extends AbstractObjectData
{
    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('esendexbatchId');
    }
}