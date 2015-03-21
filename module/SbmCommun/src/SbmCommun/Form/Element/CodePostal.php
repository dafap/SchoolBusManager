<?php
/**
 * Définition de l'élément 'CodePostal' avec filtres et validateurs
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form/Element
 * @filesource CodePostal.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 juil. 2014
 * @version 2014-1
 */
namespace SbmCommun\Form\Element;

use Zend\Form\Element;
use Zend\InputFilter\InputProviderInterface;
use Zend\Validator\Regex as RegexValidator;

class CodePostal extends Element implements InputProviderInterface
{

    const PATTERN = '/^(?:0[1-9]|[1-8][0-9]|9[0-8]|2A|2B)[0-9]{3}$/';

    /**
     *
     * @var ValidatorInterface
     */
    private $validator;

    public function getValidator()
    {
        if (is_null($this->validator)) {
            $validator = new RegexValidator(self::PATTERN);
            $validator->setMessage('Les codes postaux sont composés de 5 chiffres sauf pour la Corse où l\'on autorise 2A et 2B !', RegexValidator::NOT_MATCH);
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
                array('name' => 'Zend\Filter\StringToUpper')
            ),
            'validators' => array(
                $this->getValidator()
            )
        );
    }
}