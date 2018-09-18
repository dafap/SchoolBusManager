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
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Model\Strategy;

/*
 * @deprecated use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
 */
use Zend\Hydrator\Strategy\StrategyInterface;

class TarifAttributs implements StrategyInterface
{

    private $codes = [];

    private $error_message;

    public function __construct(array $codes, $error_message)
    {
        $this->codes = $codes;
        $this->error_message = $error_message;
    }

    public function extract($param)
    {
        if (is_int($param)) {
            if (array_key_exists($param, $this->codes)) {
                return $param;
            }
        } else {
            foreach ($this->codes as $key => $code) {
                if ($param == $code)
                    return $key;
            }
        }
        throw new Exception(sprintf($this->error_message . " : %s", $param));
    }

    public function hydrate($value)
    {
        if (array_key_exists($value, $this->codes)) {
            return $this->codes[$value];
        }
        throw new Exception(sprintf($this->error_message . " : %s", $value));
    }
}