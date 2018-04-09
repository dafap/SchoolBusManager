<?php
/**
 * Filtre mettant le premier caractère d'une chaine en lettre capitale et le reste en minuscules. 
 *
 * N'existe pas dans les fonctions multibyte
 * 
 * @project sbm
 * @package SbmCommun\Filter
 * @filesource MbUcfirst.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Filter;

use Zend\Filter\FilterInterface;

class MbUcfirst implements FilterInterface
{

    const SPACE = '#?!';

    public function filter($value)
    {
        return str_replace(self::SPACE, ' ', 
            mb_convert_case(str_replace(' ', self::SPACE, $value), MB_CASE_TITLE, 'utf-8'));
    }
}