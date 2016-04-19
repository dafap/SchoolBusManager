<?php
/**
 * Remplacement des noms de tables par leur valeur canonique
 *
 * Les chaines %table(nomtable)%, %vue(nomvue)% et %system(nomtablesysteme)% sont remplacées par le nom canonique.
 * 
 * @project sbm
 * @package SbmPdf/Model/Filter
 * @filesource NomTable.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 avr. 2016
 * @version 2016-2
 */
namespace SbmPdf\Model\Filter;

use Zend\Filter\FilterInterface;

class NomTable implements FilterInterface
{
    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;
    
    public function __construct(array $options)
    {
        $this->db_manager = $options['db_manager'];
    }
    
    public function filter($value)
    {
        $pattern = '/%([a-z]+)\(([0-9A-Za-z]*)\)%/i';
        if (preg_match_all($pattern, $value, $array)) {
            for ($i = 0; $i < count($array[0]); $i++) {
                $value = str_replace($array[0][$i], $this->db_manager->getCanonicName($array[2][$i], $array[1][$i]), $value);
            }
        }
        return $value;
    }
}