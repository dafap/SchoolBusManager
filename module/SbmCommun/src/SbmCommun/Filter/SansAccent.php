<?php
/**
 * Filtre supprimant les accents et entités html
 *
 *
 * @project sbm
 * @package SbmCommun/Filter
 * @filesource SansAccent.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 juil. 2014
 * @version 2014-1
 */
namespace SbmCommun\Filter;

use Zend\Filter\FilterInterface;

class SansAccent implements FilterInterface
{
    public function filter($val)
    {
        if (is_string($val)) {
            // traite le texte utf8
            $val = str_replace(
                array(
                    'à', 'â', 'ä', 'á', 'ã', 'å', 'a̧', 'ą', 'ⱥ', 'ǎ', 'ȧ', 'ạ', 'ā',
                    'ć', 'c̀', 'ĉ', 'c̈', 'ç', 'c̨', 'ȼ', 'č', 'ċ', 'c̣', 'c̄', 'c̃',
                    'é', 'è', 'ê', 'ë', 'ȩ', 'ę', 'ɇ', 'ě', 'ė', 'ẹ', 'ē', 'ẽ',
                    'í', 'ì', 'î', 'ï', 'i̧', 'į', 'ɨ', 'ǐ', 'i', 'ị', 'ī', 'ĩ',
                    'j́', 'j̀', 'ĵ', 'j̈', 'j̧', 'j̨', 'ɉ', 'ǰ', 'j', 'j̣', 'j̄', 'j̃',
                    'ĺ', 'l̀', 'l̂', 'l̈', 'ļ', 'l̨', 'ł', 'ƚ', 'ľ', 'l̇', 'ḷ', 'l̄', 'l̃',
                    'ń', 'ǹ', 'n̂', 'n̈', 'ņ', 'n̨', 'ň', 'ṅ', 'ṇ', 'n̄', 'ñ',
                    'ó', 'ò', 'ô', 'ö', 'o̧', 'ǫ', 'ø', 'ɵ', 'ǒ', 'ȯ',	'ọ', 'ō', 'õ',
                    'ś', 's̀', 'ŝ', 's̈', 'ş', 's̨', 'š', 'ṡ', 'ṣ', 's̄', 's̃',
                    't́', 't̀', 't̂', 'ẗ', 'ţ', 'T̨', 'Ⱦ', 'ŧ', 'ť', 'ṫ', 'ṭ', 't̄', 't̃',
                    'ú', 'ù', 'û', 'ü', 'u̧', 'ų', 'ʉ', 'ǔ', 'u̇', 'ụ', 'ū', 'ũ',
                    'ý', 'ỳ', 'ŷ', 'ÿ', 'y̧', 'y̨', 'ɏ', 'y̌', 'ẏ', 'ỵ', 'ȳ', 'ỹ',
                    'ź', 'z̀', 'ẑ', 'z̈', 'z̧', 'z̨', 'ƶ', 'ž', 'ż', 'ẓ', 'z̄', 'z̃',
                    'À', 'Â', 'Ä', 'Á', 'Ã', 'Å', 'A̧', 'Ą', 'Ⱥ', 'Ǎ', 'Ȧ', 'Ạ', 'Ā',
                    'Ć', 'C̀', 'Ĉ', 'C̈', 'Ç', 'C̨', 'Ȼ', 'Č', 'Ċ', 'C̣', 'C̄', 'C̃',
                    'É', 'È', 'Ê', 'Ë', 'Ȩ', 'Ę', 'Ɇ', 'Ě', 'Ė', 'Ẹ', 'Ē', 'Ẽ',
                    'Í', 'Ì', 'Î', 'Ï', 'I̧', 'Į', 'Ɨ', 'Ǐ', 'İ', 'Ị', 'Ī', 'Ĩ',
                    'J́', 'J̀', 'Ĵ', 'J̈', 'J̧', 'J̨', 'Ɉ', 'J̌', 'J̇', 'J̣', 'J̄', 'J̃',
                    'Ĺ', 'L̀', 'L̂', 'L̈', 'Ļ', 'L̨', 'Ł', 'Ƚ', 'Ľ', 'L̇', 'Ḷ', 'L̄', 'L̃',
                    'Ń', 'Ǹ', 'N̂', 'N̈', 'Ņ', 'N̨', 'Ň', 'Ṅ', 'Ṇ', 'N̄', 'Ñ',
                    'Ó', 'Ò', 'Ô', 'Ö', 'O̧', 'Ǫ', 'Ø', 'Ɵ', 'Ǒ', 'Ȯ', 'Ọ', 'Ō', 'Õ',
                    'Ś', 'S̀', 'Ŝ', 'S̈', 'Ş', 'S̨', 'Š', 'Ṡ', 'Ṣ', 'S̄', 'S̃',
                    'T́', 'T̀', 'T̂', 'T̈', 'Ţ', 'T̨', 'Ⱦ', 'Ŧ', 'Ť', 'Ṫ', 'Ṭ', 'T̄', 'T̃',
                    'Ú', 'Ù', 'Û', 'Ü', 'U̧', 'Ų', 'Ʉ', 'Ǔ', 'U̇', 'Ụ', 'Ū', 'Ũ',
                    'Ý', 'Ỳ', 'Ŷ', 'Ÿ', 'Y̧', 'Y̨', 'Ɏ', 'Y̌', 'Ẏ', 'Ỵ', 'Ȳ', 'Ỹ',
                    'Ź', 'Z̀', 'Ẑ', 'Z̈', 'Z̧', 'Z̨', 'Ƶ', 'Ž', 'Ż', 'Ẓ', 'Z̄', 'Z̃',
                    'æ', 'œ', 'Æ', 'Œ'
                ),
                array(
                    'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
                    'c', 'c', 'c', 'c', 'c', 'c', 'c', 'c', 'c', 'c', 'c', 'c',
                    'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
                    'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i',
                    'j', 'j', 'j', 'j', 'j', 'j', 'j', 'j', 'j', 'j', 'j', 'j',
                    'l', 'l', 'l', 'l', 'l', 'l', 'l', 'l', 'l', 'l', 'l', 'l', 'l',
                    'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n',
                    'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
                    's', 's', 's', 's', 's', 's', 's', 's', 's', 's', 's',
                    't', 't', 't', 't', 't', 't', 't', 't', 't', 't', 't', 't', 't',
                    'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
                    'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y',
                    'z', 'z', 'z', 'z', 'z', 'z', 'z', 'z', 'z', 'z', 'z', 'z',
                    'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A',
                    'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C',
                    'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E',
                    'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I',
                    'J', 'J', 'J', 'J', 'J', 'J', 'J', 'J', 'J', 'J', 'J', 'J',
                    'L', 'L', 'L', 'L', 'L', 'L', 'L', 'L', 'L', 'L', 'L', 'L', 'L',
                    'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'N',
                    'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O',
                    'S', 'S', 'S', 'S', 'S', 'S', 'S', 'S', 'S', 'S', 'S',
                    'T', 'T', 'T', 'T', 'T', 'T', 'T', 'T', 'T', 'T', 'T', 'T', 'T',
                    'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U',
                    'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y',
                    'Z', 'Z', 'Z', 'Z', 'Z', 'Z', 'Z', 'Z', 'Z', 'Z', 'Z', 'Z', 
                    'ae', 'oe', 'AE', 'OE'
                ),$val);
                // traite les entités html (?:xxx) signifie que la parenthèse n'est pas capturante dans l'expression régulière
                $val = htmlentities($val, ENT_NOQUOTES, 'utf-8');
                $val = preg_replace('#\&([A-za-z])(?:acute|cedil|circ|grave|ring|tilde|uml)\;#', '\1', $val);
                $val = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $val);
        } elseif (is_array($val)) {
            // traite les tableaux
            $vals = $val;
            $val = array();
            foreach ($vals as $key => $value) {
                $val[$this->filter($key)] = $this->filter($value);
            }
        }
        
        return $val;
    }
}