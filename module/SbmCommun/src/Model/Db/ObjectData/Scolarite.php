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
 * @date 23 avr.2019
 * @version 2019-2.5.0
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
            $value = $this->communeId;
            $value = $this->codePostal;
            $value = $this->adresseL1;
            unset($value);
            return true;
        } catch (Exception\OutOfBoundsException $e) {
            return false;
        }
    }

    /**
     * Être du district et avoir au moins un domicile à plus de 1 km de l'établissement.
     * Si la distance est 99 c'est qu'elle n'a pas pu être calculée par GoogleMaps
     *
     * @param \SbmCommun\Model\Db\ObjectData\ObjectDataInterface $this
     * @return boolean
     */
    public function avoirDroits()
    {
        return $this->district == 1 &&
            (($this->distanceR1 > 1 && $this->distanceR1 != 99) ||
            ($this->distanceR2 > 1 && $this->distanceR2 != 99));
    }
}
