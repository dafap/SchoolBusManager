<?php
/**
 * Objet contenant les données à manipuler pour la table `scolarites`
 * (à déclarer dans module.config.php)
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource Scolarite.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\ObjectData;

class Scolarite extends AbstractObjectData
{

    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName([
            'millesime',
            'eleveId'
        ]);
    }

    /**
     * Renvoie vrai si l'élève a une adresse perso notée dans sa fiche scolarité. Pour
     * cela, il doit avoir une adresseL1, un codePostal et une communeId. Il suffit donc
     * que la référence à ces propriétés ne provoque pas une exception.
     *
     * @return bool
     */
    public function hasAdressePerso()
    {
        try {
            $value = ! empty($this->communeId);
            $value &= ! empty($this->codePostal);
            $value &= ! empty($this->adresseL1);
            return $value;
        } catch (Exception\OutOfBoundsException $e) {
            return false;
        }
    }
}
