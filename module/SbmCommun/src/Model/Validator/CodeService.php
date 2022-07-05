<?php
/**
 * Validateur pour le code d'un service
 *
 * Version de la Communauté de communes MILLAU GRANDS CAUSSES
 *
 * @project sbm
 * @package SbmCommun/Model/Validator
 * @filesource CodeService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 juil. 2022
 * @version 2021-2.5.15
 */
namespace SbmCommun\Model\Validator;

use Zend\Validator\AbstractValidator;

/**
 * Il y a 3 modèles séparés par des OU dans le PATTERN (les 2 premiers modèles composés
 * d'une partie commune et d'une partie alternative)
 *
 * @author pomirol
 */
class CodeService extends AbstractValidator
{

    const PATTERN = '/^[1-9][0-9]{4}[A-Z]?$|^MGC[0-9]{2,3}[A-Z]{0,2}(?:-[A-Z][1-9])?$|^M[4][0-9]{2}[A-Z](?:-V[1-9])?$/';

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