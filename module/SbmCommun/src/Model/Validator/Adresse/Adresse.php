<?php
/**
 * Validateur d'adresse prenant en compte le zonage éventuel de la commune.
 *
 * @project sbm
 * @package SbmCommun/src/Model/Validator/Adresse
 * @filesource Adresse.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 août 2019
 * @version 2019-2.5.1
 */
namespace SbmCommun\Model\Validator\Adresse;

use Zend\Validator\AbstractValidator;

class Adresse extends AbstractValidator
{
    const PATTERN = "/^[0-9]*[ ]?(?:[A-Z]|BIS|TER|QUATER|QUINQUIES)? (.*)$/";

    const ERROR = 'adresse-inconnue';

    protected $messages = [];

    protected $messageTemplates = [
        self::ERROR => "'%value%' n'est pas une adresse référencée. Consultez la liste des adresses.",
    ];

    public function __construct()
    {

    }
    public function isValid($value, $context)
    {
    }


}