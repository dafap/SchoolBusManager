<?php
/**
 * Conversion du font_style en tableau et réciproquement
 *
 * @project sbm
 * @package SbmPdf/Model/Strategy
 * @filesource FontStyle.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmPdf\Model\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

class FontStyle implements StrategyInterface
{

    private $reference = [
        "B", // Gras
        "I", // Italique
        "U", // Souligné
        "D", // Barré
        "O"
    ]; // Trait suscrit


    public function extract($param)
    {
        $result = '';
        if (! empty($param) && is_array($param)) {
            $result = implode('', $param);
        }
        return $result;
    }

    public function hydrate($value)
    {
        $result = [];
        if (! empty($value)) {
            if (is_string($value)) {
                $result = str_split($value);
            } else {
                $result = $value;
            }
        }
        return $result;
    }
} 