<?php
/**
 * Validateur pour le code d'une ligne
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package SbmCommun/Model/Validator
 * @filesource CodeService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 fév. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Validator;

use Zend\Validator\AbstractValidator;

/**
 * Il y a 4 modèles séparés par OU dans le PATTERN<ul>
 * <li>les lignes régulières de L1 à L8</li>
 * <li>les lignes régulières de L21 à L24</li>
 * <li>la ligne L51</li>
 * <li>les lignes juniors sont composées de 3 ou 4 chiffres</li></ul>
 *
 * @author pomirol
 */
class CodeLigne extends AbstractValidator
{

    const PATTERN = '/^L[1-8]$|L[2][1-4]$|^L51$|^[1-9][0-9]{2,3}$/';

    const ERROR = 'codeService';

    protected $messageTemplates = [
        self::ERROR => "'%value%' n'a pas la forme du code d'une ligne. Consultez l'aide."
    ];

    /**
     * La méthode reçoit un tableau de 5 éléments indexés de 0 à 4
     *
     * {@inheritdoc}
     * @see \Zend\Validator\ValidatorInterface::isValid()
     */
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