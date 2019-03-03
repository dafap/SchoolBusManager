<?php
/**
 * Définition de l'élément 'Telephone' avec filtres et validateurs
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form/Element
 * @filesource Telephone.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Form\Element;

use Zend\Form\Element;
use Zend\InputFilter\InputProviderInterface;
use Zend\Validator\Regex as RegexValidator;
use Zend\Validator\ValidatorInterface;

class Telephone extends Element implements InputProviderInterface
{

    /**
     *
     * @var ValidatorInterface
     */
    protected $validator;

    public function getValidator()
    {
        if (is_null($this->validator)) {
            $validator = new RegexValidator('/^0[1-9](\s?\d{2}){4}$/');
            $validator->setMessage('Entrez les 10 chiffres composant le numéro !',
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
     * Le filtre supprime les espaces dans la chaine de caractères
     * Le validateur vérifie que le numéro est bien formé (avec ou sans espaces séparateurs)
     *
     * @return array
     */
    public function getInputSpecification()
    {
        return [
            'name' => $this->getName(),
            'required' => true,
            'filters' => [
                [
                    'name' => 'Zend\Filter\PregReplace',
                    'options' => [
                        'pattern' => '/\s/',
                        'replacement' => ''
                    ]
                ]
            ],
            'validators' => [
                $this->getValidator()
            ]
        ];
    }
}