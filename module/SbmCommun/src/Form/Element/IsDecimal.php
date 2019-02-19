<?php
/**
 * Définition de l'élément 'IsDecimal' avec filtres et validateurs
 *
 * Accepte tout nombre ayant un séparateur décimal, qu'il soit , ou .
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form/Element
 * @filesource IsDecimal.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Form\Element;

use Zend\Form\Element;
use Zend\InputFilter\InputProviderInterface;
use Zend\Validator\Regex as RegexValidator;
use Zend\Validator\ValidatorInterface;

class IsDecimal extends Element implements InputProviderInterface
{

    /**
     *
     * @var ValidatorInterface
     */
    private $validator;

    public function getValidator()
    {
        if (is_null($this->validator)) {
            $pattern = '/^\d*[\.,]?\d*$/';
            $validator = new RegexValidator($pattern);
            $validator->setMessage('Votre saisie n\'est pas un nombre décimal.',
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

    public function getInputSpecification()
    {
        return [
            'name' => $this->getName(),
            'filters' => [
                [
                    'name' => 'Zend\Filter\PregReplace',
                    'options' => [
                        'pattern' => '/,/',
                        'replacement' => '.'
                    ]
                ]
            ],
            'validators' => [
                $this->getValidator()
            ]
        ];
    }
}