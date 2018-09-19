<?php
/**
 * Filtre pour un décimal
 *
 * Ne laisse que des chiffres et un séparateur décimal.
 * Permet de préciser le séparateur décimal (. par défaut) et le caractère à remplacer par le séparateur décimal (rien par défaut)
 * 
 * @project sbm
 * @package SbmCommun/Filter
 * @filesource Decimal.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Filter;

use SbmBase\Model\StdLib;
use Zend\Filter\AbstractFilter;
use Zend\Filter\FilterInterface;

class Decimal extends AbstractFilter implements FilterInterface
{

    const PATTERN = '/[^0-9#]/';

    protected $options = [
        'separateur' => '.',
        'car2sep' => null
    ];

    private $car2sep;

    public function __construct($options)
    {
        if (! is_array($options)) {
            throw new \Exception(
                __CLASS__ .
                " - Le séparateur décimal est donné dans un tableau options => ['separateur' => ',')");
        }
        $this->car2sep = StdLib::getParam('car2sep', $options, false);
        $this->setOptions($options);
    }

    public function filter($val)
    {
        if ($this->car2sep) {
            $val = str_replace($this->options['car2sep'], $this->options['separateur'],
                $val);
        }
        $pattern = str_replace('#', $this->options['separateur'], self::PATTERN);
        return preg_replace($pattern, '', $val);
    }
}