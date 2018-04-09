<?php
/**
 * Définition de l'élément DateTimeSelect
 *
 * Cette classe surcharge la classe de Zend afin de permettre la saisie d'une valeur nulle
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form/Element
 * @filesource DateTimeSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Form\Element;

use Zend\Form\Element\DateTimeSelect as ZendDateTimeSelect;
use SbmCommun\Model\Validator\DateValidator;

class DateTimeSelect extends ZendDateTimeSelect
{

    protected function getValidator()
    {
        if (null === $this->validator) {
            $this->validator = new DateValidator(
                [
                    'format' => 'Y-m-d H:i:s'
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
                    'name' => 'DateTimeSelect',
                    'options' => [
                        'null_on_all_empty' => true
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