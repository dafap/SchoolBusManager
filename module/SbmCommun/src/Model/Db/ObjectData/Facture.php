<?php
/**
 * Objet contenant les données à manipuler pour la table `factures`
 *
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource Facture.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\ObjectData;

class Facture extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName([
            'exercice',
            'numero'
        ]);
    }
}