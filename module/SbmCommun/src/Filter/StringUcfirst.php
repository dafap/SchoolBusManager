<?php
/**
 * Filtre mettant le premier caractère de chaque mot en lettre capitale et le reste en minuscules. 
 * Les mots sont séparés par un espace ou un tiret.
 *
 *
 * @project sbm
 * @package SbmCommun/Filter
 * @filesource StringUcfirst.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Filter;

use Zend\Filter\AbstractUnicode;
use Zend\Filter\FilterInterface;

class StringUcfirst extends AbstractUnicode implements FilterInterface
{

    /**
     *
     * @var array
     */
    protected $options = [
        'encoding' => null
    ];

    protected static $exceptions = [];

    /**
     * Constructor
     *
     * @param string|array|\Traversable $encodingOrOptions
     *            OPTIONAL
     */
    public function __construct($encodingOrOptions = null)
    {
        if ($encodingOrOptions !== null) {
            if (! static::isOptions($encodingOrOptions)) {
                $this->setEncoding($encodingOrOptions);
            } else {
                $this->setOptions($encodingOrOptions);
            }
        }
    }

    public function setExceptions($array = [])
    {
        self::$exceptions = $array;
    }

    public function filter($val)
    {
        if (is_string($val)) {
            $morceaux = explode('-', $val);
            foreach ($morceaux as &$morceau) {
                $mots = explode(' ', $morceau);
                foreach ($mots as &$mot) {
                    $parties = explode('\'', $mot);
                    foreach ($parties as &$partie) {
                        $partie = $this->wordFilter($partie);
                    }
                    $mot = implode('\'', $parties);
                }
                $morceau = implode(' ', $mots);
            }
            $val = implode('-', $morceaux);
        }
        return $val;
    }

    private function wordFilter($mot)
    {
        if (! is_scalar($mot)) {
            return $mot;
        }
        $mot = (string) $mot;

        if (in_array($mot, self::$exceptions)) {
            return mb_strtolower($mot, $this->options['encoding']);
        }

        if ($this->options['encoding'] !== null) {
            return mb_convert_case($mot, MB_CASE_TITLE, $this->options['encoding']);
        }

        return ucfirst(strtolower($mot));
    }
}