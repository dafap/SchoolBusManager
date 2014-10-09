<?php
/**
 * Structure de la table système documents
 *
 *
 * @project sbm
 * @package 
 * @filesource 
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 août 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\ObjectData\Sys;

use SbmCommun\Model\Db\ObjectData\AbstractObjectData;

class Document extends AbstractObjectData
{
    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('documentId');
    }

    public function isRecordSourceTable()
    {
        return $this->recordSourceType == 'T';
    }    
}