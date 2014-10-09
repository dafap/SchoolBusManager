<?php
/**
 * Stratégie pour hydrater les champs représentant le code de paiement d'un tarif
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Strategy
 * @filesource TarifRythme.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
use SbmCommun\Model\Strategy\Exception;

class TarifAttributs implements StrategyInterface
{
    private $codes = array();
    private $error_message;
    
    public function __construct(array $codes, $error_message)
    {
        $this->codes = $codes;
        $this->error_message = $error_message;
    }
    
    public function extract($param)
    {
        if (is_int($param)) {
            return $param;
        }
        foreach ($this->codes as $key => $code) {
            if ($param == $code) return $key;
        }
        throw new Exception(sprintf($this->error_message . " : %s", $param));
    }
    
    public function hydrate($value)
    {
        return $this->codes[$value];
    }
}