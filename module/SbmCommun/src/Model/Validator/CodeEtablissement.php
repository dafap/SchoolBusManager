<?php
/**
 * Validateur pour le code d'un établissement
 *
 * De la forme '0ddnnnnL' où
 *  dd représente le numéro du département
 *  nnnn représente un entier à 4 chiffres
 *  L représente une lettre
 * Par dérogation, pour les annexes, remplacer le 0 de tête par une lettre majuscule en partant de la lettre A 
 * 
 * @project sbm
 * @package SbmCommun/Model/Validator
 * @filesource CodeEtablissement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Validator;

use Zend\Validator\AbstractValidator;

class CodeEtablissement extends AbstractValidator
{

    const PATTERN = '/^[0A-Z](?:[0-9]{2}|2A)[0-9]{4}[A-Z]$/';

    const ERROR = 'codeEtablissement';

    protected $messageTemplates = [
        self::ERROR => "'%value%' n'a pas la forme d'un code d'établissement. Consultez l'aide."
    ];

    public function isValid($value)
    {
        $this->setValue($value);

        if (! preg_match(self::PATTERN, $value)) {
            $this->error(self::ERROR);
            return false;
        }

        return true;
    }
}