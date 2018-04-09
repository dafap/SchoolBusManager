<?php
/**
 * Structure de la table système doclabels
 *
 * Attention ! La liaison entre les tables `doclabels` et `document` étant de type 0<->1
 * on remplace la clé primaire pas documentId.
 * 
 * @project sbm
 * @package SbmCommun\Model\Db\ObjectData\Sys
 * @filesource DocLabel.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\ObjectData\Sys;

use SbmCommun\Model\Db\ObjectData\AbstractObjectData;

class DocLabel extends AbstractObjectData
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
 