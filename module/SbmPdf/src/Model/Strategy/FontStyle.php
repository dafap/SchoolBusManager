<?php
/**
 * Conversion du font_style en tableau et réciproquement
 *
 * @project sbm
 * @package SbmPdf/Model/Strategy
 * @filesource FontStyle.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 fév. 2019
 * @version 2018-2.5.0
 */
namespace SbmPdf\Model\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

class FontStyle implements StrategyInterface
{

    /**
     * Dans l'ordre : Gras, Italique, Souligné, Barré, Trait suscrit
     *
     * @var array
     */
    private $reference = [
        "B",
        "I",
        "U",
        "D",
        "O"
    ];

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