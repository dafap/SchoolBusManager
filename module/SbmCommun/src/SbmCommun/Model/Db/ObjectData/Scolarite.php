<?php
/**
 * Objet contenant les données à manipuler pour la table `scolarites`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource Scolarite.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\ObjectData;

class Scolarite extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName(
            [
                'millesime',
                'eleveId'
            ]);
    }

    /**
     * Renvoie vrai si l'élève a une adresse perso notée dans sa fiche scolarité.
     * Pour cela, il doit avoir une addresseL1, un codePostal et une communeId.
     *
     * @return bool
     */
    public function hasAdressePerso()
    {
        try {
            $value = $this->communeId;
            $value = $this->codePostal;
            $value = $this->adresseL1;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
 