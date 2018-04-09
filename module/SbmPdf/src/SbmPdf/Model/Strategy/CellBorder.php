<?php
/**
 * Conversion du cell_border en tableau et réciproquement
 *
 * @project sbm
 * @package SbmPdf/Model/Strategy
 * @filesource CellBorder.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmPdf\Model\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

class CellBorder implements StrategyInterface
{

    private $reference = [
        'exclusif' => [
            0,
            1
        ],
        'combine' => [
            'L',
            'T',
            'R',
            'B'
        ]
    ];

    public function extract($param)
    {
        $result = '';
        if (! empty($param) && is_array($param)) {
            if (in_array('-1', $param)) {
                $result = '0';
            } elseif (in_array('1', $param)) {
                $result = '1';
            } else {
                $result = implode('', $param);
            }
        }
        return empty($result) ? '0' : $result;
    }

    public function hydrate($value)
    {
        $result = [];
        if (empty($value) || $value == '0') {
            $result = [
                '-1'
            ];
        } elseif (is_string($value)) {
            $result = str_split($value);
        } else {
            $result = $value;
        }
        return $result;
    }
}
 