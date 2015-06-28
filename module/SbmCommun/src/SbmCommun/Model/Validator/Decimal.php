<?php
/**
 * Validateur pour un nombre décimal positif (qui peut être entier)
 *
 * Cela fonctionne que le séparateur décimal soit , ou .
 * 
 * @project sbm
 * @package SbmCommun/Model/Validator
 * @filesource Decimal.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 mars 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Validator;

use Zend\Validator\AbstractValidator;

class Decimal extends AbstractValidator
{
    const PATTERN = '/^[0-9]+(?:[.,][0-9]+)?$/';
    const FLOAT = 'float';
    
    protected $messageTemplates = array(
        self::FLOAT => "'%value%' n'est pas un décimal positif."
    );
    
    public function isValid($value)
    {
        $this->setValue($value);
        if (is_float($value)) {
            return true;
        }
               
        if (!preg_match(self::PATTERN, $value)) {
            $this->error(self::FLOAT);
            return false;
        }
    
        return true;
    }
}
