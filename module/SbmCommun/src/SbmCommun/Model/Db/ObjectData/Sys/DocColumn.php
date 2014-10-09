<?php
/**
 * Structure de la table système doccolumns
 *
 * @project sbm
 * @package SbmCommun\Model\Db\ObjectData\Sys
 * @filesource DocColumn.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 sept. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\ObjectData\Sys;

use SbmCommun\Model\Db\ObjectData\AbstractObjectData;

class DocColumn extends AbstractObjectData
{
    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('doccolumnId');
    }

    public function isRecordSourceTable()
    {
        return $this->recordSourceType == 'T';
    }
}
 