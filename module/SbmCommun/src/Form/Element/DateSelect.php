<?php
/**
 * Définition de l'élément DateSelect
 *
 * Cette classe surcharge la classe de Zend afin de permettre la saisie d'une valeur nulle
 *
 * @project sbm
 * @package module/SbmCommun/src/Form/Element
 * @filesource DateSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mai 2019
 * @version 2018-2.5.0
 */
namespace SbmCommun\Form\Element;

use SbmCommun\Model\Validator\DateValidator;
use Zend\Form\Element\DateSelect as ZendDateSelect;

class DateSelect extends ZendDateSelect
{

    protected function getValidator()
    {
        if (null === $this->validator) {
            $this->validator = new DateValidator(
                [
                    'format' => 'Y-m-d'
                ]);
        }
        return $this->validator;
    }

    public function getInputSpecification()
    {
        return [
            'name' => $this->getName(),
            'required' => false,
            'filters' => [
                [
                    'name' => 'DateSelect',
                    'options' => [
                        'null_on_empty' => true
                    ]
                ]
            ],
            'validators' => [
                $this->getValidator()
            ]
        ];
    }

    public function setValue($value)
    {
        if (is_null($value))
            return $this;
        return parent::setValue($value);
    }
}