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
 * @date 31 mai 2020
 * @version 2020-2.6.0
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

    /**
     *
     * {@inheritdoc}
     * @see \Zend\Hydrator\Strategy\StrategyInterface::extract()
     *
     * @throws \SbmCommun\Model\Strategy\Exception\RuntimeException
     */
    public function extract($param)
    {
        $param = is_numeric($param) ? (int) $param : $param;
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
        throw new Exception\RuntimeException(
            sprintf($this->error_message . " : %s", $param));
    }

    /**
     *
     * {@inheritdoc}
     * @see \Zend\Hydrator\Strategy\StrategyInterface::hydrate()
     *
     * @throws \SbmCommun\Model\Strategy\Exception\RuntimeException
     */
    public function hydrate($value)
    {
        if (is_null($value)) {
            return $value;
        } elseif (array_key_exists($value, $this->codes)) {
            return $this->codes[$value];
        }
        throw new Exception\RuntimeException(
            sprintf($this->error_message . " : %s", $value));
    }
}