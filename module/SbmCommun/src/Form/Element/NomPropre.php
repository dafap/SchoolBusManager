<?php
/**
 * Définition de l'élément 'NomPropre' avec filtres et validateurs
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form/Element
 * @filesource NomPropre.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Form\Element;

use Zend\Form\Element;
use Zend\InputFilter\InputProviderInterface;
use Zend\Validator\Regex as RegexValidator;
use Zend\Validator\ValidatorInterface;

class NomPropre extends Element implements InputProviderInterface
{

    const LETTRES = '[A-ZÀÂÄÁÃÅA̧ĄȺǍȦẠĀĆC̀ĈC̈ÇC̨ȻČĊC̣C̄C̃ÉÈÊËȨĘɆĚĖẸĒẼÍÌÎÏI̧ĮƗǏİỊĪĨJ́J̀ĴJ̈J̧J̨ɈJ̌J̇J̣J̄J̃ĹL̀L̂L̈ĻL̨ŁȽĽL̇ḶL̄L̃ŃǸN̂N̈ŅN̨ŇṄṆN̄ÑÓÒÔÖO̧ǪØƟǑȮỌŌÕŚS̀ŜS̈ŞS̨ŠṠṢS̄S̃T́T̀T̂T̈ŢT̨ȾŦŤṪṬT̄T̃ÚÙÛÜU̧ŲɄǓU̇ỤŪŨÝỲŶŸY̧Y̨ɎY̌ẎỴȲỸŹZ̀ẐZ̈Z̧Z̨ƵŽŻẒZ̄Z̃ÆŒ]+';

    /**
     *
     * @var ValidatorInterface
     */
    private $validator;

    public function getValidator()
    {
        if (is_null($this->validator)) {
            $pattern = '/^' . self::LETTRES . '([\' -]' . self::LETTRES . ')*$/';
            $validator = new RegexValidator($pattern);
            $validator->setMessage(
                'Les caractères autorisés sont des lettres, l\'espace, l\'apostrophe ou le tiret !',
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
        return [
            'name' => $this->getName(),
            'required' => true,
            'filters' => [
                // problème de l'apostrophe si échappement des caractères
                [
                    'name' => 'Zend\Filter\Callback',
                    'options' => [
                        'callback' => 'stripslashes'
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
                        'pattern' => '/[-]+/',
                        'replacement' => '-'
                    ]
                ],
                [
                    'name' => 'Zend\Filter\PregReplace',
                    'options' => [
                        'pattern' => '/[\']+/',
                        'replacement' => '\''
                    ]
                ],
                // supprime les balises html
                [
                    'name' => 'Zend\Filter\StripTags'
                ],
                // supprime les espaces en début et en fin
                [
                    'name' => 'Zend\Filter\StringTrim'
                ],
                // met en majuscules, y compris les lettres accentuées et ligatures
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