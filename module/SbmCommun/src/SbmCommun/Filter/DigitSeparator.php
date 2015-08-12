<?php
/**
 * Filtre les caractères autorisés pour une année scolaire et remplace /, _ ou espace par un tiret -
 *
 * @project sbm
 * @package SbmCommun/Filter
 * @filesource DigitSeparator.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 août 2015
 * @version 2015-1
 */
namespace SbmCommun\Filter;

use Zend\Filter\AbstractFilter;
use Zend\Filter\FilterInterface;

class DigitSeparator extends AbstractFilter implements FilterInterface
{

    const PATTERN = '/[^0-9\/-_ ]/';

    public function filter($val)
    {
        $result = preg_replace(self::PATTERN, '', $val);
        return str_replace(array('/', '_', ' '), '-', $result);
    }
}