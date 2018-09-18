<?php
/**
 * Validateur pour le code d'un service
 *
 * 1. De la forme 'nnn-n{1,2}' ou 'nnn-n{1,2}#' ou 'nnn-n{1,2}#-R' où
 *  n représente un chiffre
 *  n{1,2} représente 1 ou 2 chiffres
 *  # représente une lettre
 *  - et R sont les caractères '-' et 'R'
 * On ne contrôle pas si nnn représente les 3 caractères de fin du code de la ville de destination. 
 * 
 * 2. De la forme 'nnn-TA' ou 'nnn-TB' ou 'nnn-TC' pour les lignes régulières du TUB
 * 
 * 3. De la forme 'M2nn#' pour les circuits du CG12
 * 
 * @project sbm
 * @package SbmCommun/Model/Validator
 * @filesource CodeService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Model\Validator;

use Zend\Validator\AbstractValidator;

/**
 * Il y a 3 modèles séparés par des OU dans le PATTERN
 * (les 2 premiers modèles composés d'une partie commune et d'une partie alternative)
 *
 * @author pomirol
 *        
 */
class CodeService extends AbstractValidator
{

    const PATTERN = '/^[0-9]{3}-(?:[0-9]{1,2}[A-Z]?(?:-R)?|T[A-C])$|^M2[0-9]{2}[A-Z]$/';

    const ERROR = 'codeService';

    protected $messageTemplates = [
        self::ERROR => "'%value%' n'a pas la forme du code d'un service. Consultez l'aide."
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