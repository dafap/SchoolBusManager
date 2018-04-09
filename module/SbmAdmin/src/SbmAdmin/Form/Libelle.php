<?php
/**
 * Formulaire de saisie et modification d'un `libelle`
 *
 *
 * @project sbm
 * @package module/SbmAdmin/src/SbmAdmin/Form
 * @filesource Libelle.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr]
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmAdmin\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;
// use Zend\Validator;
class Libelle extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct($param = 'libelle')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'name' => 'id',
                'type' => 'hidden'
            ]);
        $this->add(
            [
                'name' => 'csrf',
                'type' => 'Zend\Form\Element\Csrf',
                'options' => [
                    'csrf_options' => [
                        'timeout' => 180
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'nature',
                'type' => 'text',
                'attributes' => [
                    'id' => 'libelle-nature',
                    'class' => 'sbm-width-20c'
                ],
                'options' => [
                    'label' => 'Nature',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'code',
                'type' => 'text',
                'attributes' => [
                    'id' => 'libelle-code',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Code',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'libelle',
                'type' => 'Zend\Form\Element\Textarea',
                'attributes' => [
                    'id' => 'libelle-libelle',
                    'class' => 'sbm-note'
                ],
                'options' => [
                    'label' => 'Libellé',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'ouvert',
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => [
                    'id' => 'libelle-ouvert'
                ],
                'options' => [
                    'label' => 'Ouvert',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'use_hidden_element' => true,
                    'checked_value' => '1',
                    'unchecked_value' => '0'
                ]
            ]);
        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Enregistrer',
                    'id' => 'libelle-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'libelle-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'nature' => [
                'name' => 'nature',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'Zend\I18n\Filter\Alnum'
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'Zend\Validator\StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 20
                        ]
                    ]
                ]
            ],
            'code' => [
                'name' => 'code',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\Digits'
                    ]
                ]
            ]
        ];
    }
}