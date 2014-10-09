<?php
/**
 * Stratégie pour hydrater les champs représentant les jours de la semaine
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Strategy
 * @filesource Semaine.php
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Strategy;

class Semaine extends AbstractPower2
{

    /**
     * La semaine représente un ensemble de jours.
     *
     * Son codage est établi en composant par puissance de 2 les valeurs du tableau $param
     * 1 pour lundi
     * 2 pour mardi
     * 4 pour mercredi
     * 8 pour jeudi
     * 16 pour vendredi
     * 32 pour samedi
     * 64 pour dimanche
     * 128 inutilisé
     * 127 pour la semaine complète
     */
    const NOMBRE_DE_CODES = 7;

    const CODE_SEMAINE_LUNDI = 1;

    const CODE_SEMAINE_MARDI = 2;

    const CODE_SEMAINE_MERCREDI = 4;

    const CODE_SEMAINE_JEUDI = 8;

    const CODE_SEMAINE_VENDREDI = 16;

    const CODE_SEMAINE_SAMEDI = 32;

    const CODE_SEMAINE_DIMANCHE = 64;

    const CODE_TOUS = 127;

    public function hydrate($value)
    {
        $this->nombre_de_codes = self::NOMBRE_DE_CODES;
        return parent::hydrate($value);
    }
    
    /**
     * Vérifie que la valeur est valide
     *
     * @param int $value
     *            valeur à tester
     *
     * @return boolean
     */
    protected function valid($value)
    {
        return in_array($value, array(
            self::CODE_SEMAINE_LUNDI,
            self::CODE_SEMAINE_MARDI,
            self::CODE_SEMAINE_MERCREDI,
            self::CODE_SEMAINE_JEUDI,
            self::CODE_SEMAINE_VENDREDI,
            self::CODE_SEMAINE_SAMEDI,
            self::CODE_SEMAINE_DIMANCHE
        ));
    }
}