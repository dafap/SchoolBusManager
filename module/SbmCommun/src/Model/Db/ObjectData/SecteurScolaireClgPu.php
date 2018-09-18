<?php
/**
 * Objet contenant les données à manipuler pour la table SecteursScolairesClgPu
 *
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource SecteurScolaireClgPu.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 avr. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\ObjectData;

class SecteurScolaireClgPu extends AbstractObjectData
{
    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName(array('communeId', 'etablissementId'));
    }
}