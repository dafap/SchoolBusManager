<?php
/**
 * Validator pour mot de passe
 *
 * Indique le nombre minimum de caractères et les obligations
 * - nombre minimum de majuscules
 * - nombre minimum de minuscules
 * - nombre minimum de chiffres
 * - nombre minimum de caractères spéciaux
 * 
 * @project sbm
 * @package SbmFront/Model/Validator
 * @filesource Mdp.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmFront\Model\Validator;

use Zend\Validator\AbstractValidator;

class Mdp extends AbstractValidator
{

    const LENGTH = 'length';

    const UPPER = 'upper';

    const LOWER = 'lower';

    const DIGIT = 'digit';

    const SPECIAL = 'special';

    protected $messageTemplates = [
        self::LENGTH => "Trop court. Nombre minimum de caractères : %len%",
        self::UPPER => "Invalide. Nombre minimum de lettres majuscules : %maj%",
        self::LOWER => "Invalide. Nombre minimum de lettres minuscules : %min%",
        self::DIGIT => "Invalide. Nombre minimum de caractères numériques : %num%",
        self::SPECIAL => "Invalide. Il faut %spe% caractère(s) autre que des lettres ou des chiffres."
    ];

    /**
     *
     * @var array
     */
    protected $messageVariables = [
        'len' => [
            'options' => 'len'
        ],
        'min' => [
            'options' => 'min'
        ],
        'maj' => [
            'options' => 'maj'
        ],
        'num' => [
            'options' => 'num'
        ],
        'spe' => [
            'options' => 'spe'
        ]
    ];

    protected $options = [
        'len' => 6, // Minimum length
        'min' => 1, // Minimum lowercase number
        'maj' => 1, // Mimimum uppercase number
        'num' => 1, // Minimum digit number
        'spe' => 0
    ];

    // Minimum special char number
    public function __construct($options = [])
    {
        $temp = [];
        if (! is_array($options)) {
            $options = func_get_args();
            $temp['len'] = array_shift($options);
            if (! empty($options)) {
                $temp['min'] = array_shift($options);
            }
            if (! empty($options)) {
                $temp['maj'] = array_shift($options);
            }
            if (! empty($options)) {
                $temp['num'] = array_shift($options);
            }
            if (! empty($options)) {
                $temp['spe'] = array_shift($options);
            }

            $options = $temp;
        }
        parent::__construct($options);
    }

    public function isValid($value)
    {
        $this->setValue($value);

        if (strlen($value) < $this->options['len']) {
            $this->error(self::LENGTH);
            return false;
        }

        if ($this->options['min']) {
            $tmp = preg_replace('/[^a-z]/', '', $value);
            if (strlen($tmp) < $this->options['min']) {
                $this->error(self::LOWER);
                return false;
            }
        }
        if ($this->options['maj']) {
            $tmp = preg_replace('/[^A-Z]/', '', $value);
            if (strlen($tmp) < $this->options['maj']) {
                $this->error(self::UPPER);
                return false;
            }
        }
        if ($this->options['num']) {
            $tmp = preg_replace('/[^0-9]/', '', $value);
            if (strlen($tmp) < $this->options['num']) {
                $this->error(self::DIGIT);
                return false;
            }
        }
        if ($this->options['spe']) {
            $tmp = preg_replace('/[0-9A-Za-z]/', '', $value);
            if (strlen($tmp) < $this->options['spe']) {
                $this->error(self::DIGIT);
                return false;
            }
        }
        return true;
    }
}
 