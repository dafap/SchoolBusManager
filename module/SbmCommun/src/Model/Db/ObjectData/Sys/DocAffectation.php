<?php
/**
 * Structure de la table système docaffectations
 *
 *
 * @project sbm
 * @package SbmCommun\Model\Db\ObjectData\Sys
 * @filesource DocAffectation.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\ObjectData\Sys;

use SbmCommun\Model\Db\ObjectData\AbstractObjectData;

class DocAffectation extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('docaffectationId');
    }

    public function isRecordSourceTable()
    {
        return $this->recordSourceType == 'T';
    }
}