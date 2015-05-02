<?php
/**
 * Stratégie pour hydrater les champs représentant les niveaux d'enseignement
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Strategy
 * @filesource Niveau.php
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Strategy;

class Niveau extends AbstractPower2 
{

    /**
     * Les niveaux sont établis en composant par puissance de 2 les valeurs du tableau $param 
     * 1 pour maternelle 
     * 2 pour élémentaire 
     * 4 pour premier cycle du second degré (collège, segpa) 
     * 8 pour second cycle du second degré (lycée, lp, ...) 
     * 16 pour classes après bac de lycée et lp (bts, cpge ...) 
     * 32 pour enseignement supérieur (iut, université ...) 
     * 64 pour autres (stagiaires de la formation prof, apprentis ...) 
     * 128 inutilisé 
     * 255 pour tous les niveaux 
     */
    const NOMBRE_DE_CODES = 7;

    const CODE_NIVEAU_MATERNELLE = 1;

    const CODE_NIVEAU_ELEMENTAIRE = 2;

    const CODE_NIVEAU_PREMIER_CYCLE = 4;

    const CODE_NIVEAU_SECOND_CYCLE = 8;

    const CODE_NIVEAU_POST_BAC = 16;

    const CODE_NIVEAU_SUPERIEUR = 32;

    const CODE_NIVEAU_AUTRE = 64;

    const CODE_TOUS = 127;

    public function hydrate($value, $data = null)
    {
        $this->nombre_de_codes = self::NOMBRE_DE_CODES;
        return parent::hydrate($value, $data);
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
        return array_key_exists($value, self::getNiveaux());
    }
    
    /**
     * Renvoie la liste des niveaux sous forme d'un tableau indexé
     *
     * @return array
     * La clé est le code du niveau, la valeur est le niveau
     */
    public static function getNiveaux()
    {
        return  array(
            self::CODE_NIVEAU_MATERNELLE => 'maternelle',
            self::CODE_NIVEAU_ELEMENTAIRE => 'élémentaire',
            self::CODE_NIVEAU_PREMIER_CYCLE => 'premier cycle',
            self::CODE_NIVEAU_SECOND_CYCLE => 'second cycle',
            self::CODE_NIVEAU_POST_BAC => 'post bac',
            self::CODE_NIVEAU_SUPERIEUR => 'ens. supérieur',
            self::CODE_NIVEAU_AUTRE => 'autres'
        );
    }
    
    /**
     * Renvoie la liste des niveaux sous forme d'un tableau indexé
     *
     * @return array
     * La clé est le niveau, la valeur est le code du niveau
     */
    public static function getCodesNiveaux()
    {
        return array_flip(self::getNiveaux());
    }
}