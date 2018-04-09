<?php
/**
 * Validateur pour un date qui peut être nulle
 *
 * @project sbm
 * @package SbmCommun/Model/Validator
 * @filesource DateValidator.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Validator;

use Zend\Validator\Date;

class DateValidator extends Date
{

    public function isValid($value)
    {
        if (is_null($value))
            return true;
        return parent::isValid($value);
    }
}
