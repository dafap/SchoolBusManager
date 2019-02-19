<?php
/**
 * Définition de l'élément 'Adresse' avec filtres et validateurs
 *
 * Version qui met l'adresse en majuscules
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form/Element
 * @filesource Adresse.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Form\Element;

use Zend\Form\Element;
use Zend\InputFilter\InputProviderInterface;
use Zend\Validator\Regex as RegexValidator;
use Zend\Validator\ValidatorInterface;

class Adresse extends Element implements InputProviderInterface
{

    const MAJ_AUTORISEES = 'ÀÂÄÇÉÈÊËÎÏÔÖÙÛÜŸÆŒ';

    /**
     *
     * @var ValidatorInterface
     */
    private $validator;

    public function getValidator()
    {
        if (is_null($this->validator)) {
            $premiere = '[1-9A-Z' . self::MAJ_AUTORISEES . ']';
            // $suite = '[0-9A-Za-z' . self::MAJ_AUTORISEES . mb_strtolower(self::MAJ_AUTORISEES,
            // 'utf-8') . '\' ]*';
            $suite = '[0-9A-Za-z' . self::MAJ_AUTORISEES . '\' ]*';
            $pattern = '/^' . $premiere . $suite . '$/';
            $validator = new RegexValidator($pattern);
            $validator->setMessage(
                'Les caractères autorisés sont des lettres, l\' espace ou l\' apostrophe !',
                RegexValidator::NOT_MATCH);
            $this->validator = $validator;
        }
        return $this->validator;
    }

    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * Provide default input rules for this element
     *
     * Attaches an telephone validator.
     *
     * @return array
     */
    public function getInputSpecification()
    {
        $caracteres = '0-9A-Za-z' . self::MAJ_AUTORISEES .
            mb_strtolower(self::MAJ_AUTORISEES, 'utf-8') . '\' ';
        return [
            'name' => $this->getName(),
            'required' => false,
            'filters' => [
                // problème de l'apostrophe si échappement des caractères
                [
                    'name' => 'Zend\Filter\Callback',
                    'options' => [
                        'callback' => 'stripslashes'
                    ]
                ],
                // supprime les balises html
                [
                    'name' => 'Zend\Filter\StripTags'
                ],
                // supprime les caractères non autorisés ($suite)
                [
                    'name' => 'Zend\Filter\PregReplace',
                    'options' => [
                        'pattern' => '/[^' . $caracteres . ']/',
                        'replacement' => ' '
                    ]
                ],
                // supprime les espaces multiples, tirets multiples, apostrophes multiples
                [
                    'name' => 'Zend\Filter\PregReplace',
                    'options' => [
                        'pattern' => '/[ ]+/',
                        'replacement' => ' '
                    ]
                ],
                [
                    'name' => 'Zend\Filter\PregReplace',
                    'options' => [
                        'pattern' => '/[\']+/',
                        'replacement' => '\''
                    ]
                ],
                // supprime les espaces en début et en fin
                [
                    'name' => 'Zend\Filter\StringTrim'
                ],
                // abreviations
                [
                    'name' => 'SbmCommun\Filter\Abreviations',
                    'options' => [
                        'encoding' => 'utf-8',
                        'seuil' => 38
                    ]
                ],
                // met en majuscules, y compris les lettres accentuées et ligatures
                // ['name' => 'SbmCommun\Filter\StringUcfirst', 'options' => ['encoding' =>
                // 'utf-8', 'exceptions' => ['d', 'de', 'des', 'du', 'l', 'le', 'les', 'la', 'un',
                // 'une']]]
                [
                    'name' => 'Zend\Filter\StringToUpper',
                    'options' => [
                        'encoding' => 'utf-8'
                    ]
                ]
            ],
            'validators' => [
                $this->getValidator()
            ]
        ];
    }
}