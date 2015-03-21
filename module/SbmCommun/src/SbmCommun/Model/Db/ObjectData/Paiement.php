<?php
/**
 * Objet contenant les données à manipuler pour la table `Paiements`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource Paiement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 jan. 2015
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\ObjectData;

class Paiement extends AbstractObjectData
{
    public function __construct() 
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('paiementId');
    }
}