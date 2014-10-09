<?php
/**
 * Structure de la table système documents
 *
 *
 * @project sbm
 * @package SbmCommun\Model\Db\ObjectData\Sys
 * @filesource DocTable.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 sept. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\ObjectData\Sys;

use SbmCommun\Model\Db\ObjectData\AbstractObjectData;

class DocTable extends AbstractObjectData
{
    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('doctableId');
    }

    public function isRecordSourceTable()
    {
        return $this->recordSourceType == 'T';
    }    
}