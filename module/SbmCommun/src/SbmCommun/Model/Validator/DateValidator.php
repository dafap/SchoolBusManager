<?php
/**
 * Validateur pour un date qui peut être nulle
 *
 * @project sbm
 * @package SbmCommun/Model/Validator
 * @filesource DateValidator.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 mai 2015
 * @version 2015-1
 */

namespace SbmCommun\Model\Validator;

use Zend\Validator\Date;

class DateValidator extends Date
{
    public function isValid($value)
    {
        if (is_null($value)) return true;
        return parent::isValid($value);
    }
}
