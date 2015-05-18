<?php
/**
 * Objet contenant les données à manipuler pour la table Affectations
 * (à déclarer dans module.config.php)
 *
 * Il s'agit des appels à la plateforme de paiement pour essayer de payer.
 * Cette table établit la liaison entre le payeur et les élèves concernés.
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource Appel.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 mai 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\ObjectData;

class Appel extends AbstractObjectData
{
    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('appelId');
    }
} 