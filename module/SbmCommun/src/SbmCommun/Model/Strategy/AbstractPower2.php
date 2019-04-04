<?php
/**
 * Classe abstraite pour les encodages en puissances de 2
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Strategy
 * @filesource AbstractPower2.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2019
 * @version 2019-2.4.8
 */
namespace SbmCommun\Model\Strategy;

/*
 * @deprecated use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
 */
use Zend\Hydrator\Strategy\StrategyInterface;
use SbmCommun\Model\Strategy\Exception;

abstract class AbstractPower2 implements StrategyInterface
{

    const CODE_TOUS = 0;

    protected $nombre_de_codes = 0;

    protected abstract function valid($value);

    /**
     * Reçoit un tableau d'entiers valides et renvoie un entier représentant les niveaux d'enseignement.
     *
     * @param array $param
     *            Valeur valide ou tableau d'entiers
     *            
     * @return int Code numérique indiquant les niveaux d'enseignement
     *        
     * @throws Exception
     */
    public function extract($param)
    {
        if (is_string($param)) {
            if (! $this->valid($param)) {
                throw new Exception(
                    __METHOD__ .
                         sprintf(
                            _(
                                " Le paramètre est invalide dans %s.<pre>%s</pre>\nUne puissance de 2 est attendue."), 
							get_class($this), $dump));
            }
            return $param;
        } elseif (! is_array($param)) {
            ob_start();
            var_dump($param);
            $dump = ob_get_contents();
            ob_end_clean();
            throw new Exception(
                __METHOD__ .
                     sprintf(
                        _(
                            " Le paramètre est invalide dans %s.<pre>%s</pre>\nUn tableau d'entiers puissances de 2 est attendu."), 
                        get_class($this), $dump));
        }
        $result = 0;
        foreach ($param as $value) {
            if (! (is_string($value) || is_numeric($value)) || ! $this->valid($value)) {
                if (is_string($value)) {
                    $dump = $value;
                } else {
                    ob_start();
                    print_r($value);
                    $dump = html_entity_decode(strip_tags(ob_get_clean()));
                }
                throw new Exception(
                    __METHOD__ .
                         sprintf(
                            _(
                                " Le tableau donné en paramètre dans %s  contient la valeur illégale : %s\nLes valeurs doivent être des entiers puissance de 2."), 
                            get_class($this), $dump));
            }
            $result |= (int) $value; // bitwise Or
        }
        return $result;
    }

    /**
     * Renvoie un tableau d'entiers valides (maximum NOMBRE_DE_CODES)
     *
     * @param int $value
     *            Valeur à décoder sous forme de tableau de puissances de 2 représentant les niveaux d'enseignement
     *            
     * @return array Tableau d'entiers valides
     */
    public function hydrate($value)
    {
        $tableau = [];
        for ($j = 0; $j < $this->nombre_de_codes; $j ++) {
            $element = $value & (1 << $j); // bitwise And
            if ($element != 0) {
                $tableau[] = $element;
            }
        }
        return $tableau;
    }
}