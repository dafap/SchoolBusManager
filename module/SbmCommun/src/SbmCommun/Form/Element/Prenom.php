<?php
/**
 * Définition de l'élément 'Prenom' avec filtres et validateurs
 *
 * Version qui met le prénom en majuscules
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form/Element
 * @filesource Prenom.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 mai 2015
 * @version 2015-2
 */
namespace SbmCommun\Form\Element;

use Zend\Form\Element;
use Zend\InputFilter\InputProviderInterface;
use Zend\Validator\Regex as RegexValidator;

class Prenom extends Element implements InputProviderInterface
{
    const MAJ_DIACRITIQUES = 'ÀÂÄÁÃÅA̧ĄȺǍȦẠĀĆC̀ĈC̈ÇC̨ȻČĊC̣C̄C̃ÉÈÊËȨĘɆĚĖẸĒẼÍÌÎÏI̧ĮƗǏİỊĪĨJ́J̀ĴJ̈J̧J̨ɈJ̌J̇J̣J̄J̃ĹL̀L̂L̈ĻL̨ŁȽĽL̇ḶL̄L̃ŃǸN̂N̈ŅN̨ŇṄṆN̄ÑÓÒÔÖO̧ǪØƟǑȮỌŌÕŚS̀ŜS̈ŞS̨ŠṠṢS̄S̃T́T̀T̂T̈ŢT̨ȾŦŤṪṬT̄T̃ÚÙÛÜU̧ŲɄǓU̇ỤŪŨÝỲŶŸY̧Y̨ɎY̌ẎỴȲỸŹZ̀ẐZ̈Z̧Z̨ƵŽŻẒZ̄Z̃ÆŒ';
    
    /**
     *
     * @var ValidatorInterface
     */
    private $validator;
    
    public function getValidator()
    {
        if (is_null($this->validator)) {
            $premiere = '[A-Z' . self::MAJ_DIACRITIQUES . ']';
            //$suite = '[a-z' . mb_strtolower(self::MAJ_DIACRITIQUES, 'utf-8') . ']*';
            $suite = '[A-Z' . self::MAJ_DIACRITIQUES . ']*';
            $mot = $premiere . $suite;
            $pattern = '/^' . $mot . '([\' -]' . $mot .')*$/';
            $validator = new RegexValidator($pattern);
            $validator->setMessage('Les caractères autorisés sont des lettres, l\'espace, l\'apostrophe ou le tiret !', RegexValidator::NOT_MATCH);
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
        return array(
            'name' => $this->getName(),
            'required' => true,
            'filters' => array(
                // problème de l'apostrophe si échappement des caractères
                array('name' => 'Zend\Filter\Callback', 'options' => array('callback' => 'stripslashes')),
                // supprime les espaces multiples, tirets multiples, apostrophes multiples
                array('name' => 'Zend\Filter\PregReplace', 'options' => array('pattern' => '/[ ]+/', 'replacement' => ' ')),
                array('name' => 'Zend\Filter\PregReplace', 'options' => array('pattern' => '/[-]+/', 'replacement' => '-')),
                array('name' => 'Zend\Filter\PregReplace', 'options' => array('pattern' => '/[\']+/', 'replacement' => '\'')),
                // supprime les balises html
                array('name' => 'Zend\Filter\StripTags'),
                // supprime les espaces en début et en fin
                array('name' => 'Zend\Filter\StringTrim'),
                // met en majuscules, y compris les lettres accentuées et ligatures
                //array('name' => 'SbmCommun\Filter\StringUcfirst', 'options' => array('encoding' => 'utf-8'))
                array('name' => 'Zend\Filter\StringToUpper', 'options' => array('encoding' => 'utf-8'))
            ),
            'validators' => array(
                $this->getValidator(),
            ),
        );
    }
}