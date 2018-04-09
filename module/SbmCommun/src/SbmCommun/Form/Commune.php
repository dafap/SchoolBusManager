<?php
/**
 * Formulaire de saisie et modification d'une commune
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Commune.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Commune extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('commune');
        $this->setAttribute('method', 'post');
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
                'name' => 'communeId',
                'type' => 'text',
                'attributes' => [
                    'id' => 'commune-codeid',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Code INSEE de la commune',
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
                'name' => 'nom',
                'type' => 'text',
                'attributes' => [
                    'id' => 'commune-nom',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Nom de la commune en majuscules',
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
                'name' => 'nom_min',
                'type' => 'text',
                'attributes' => [
                    'id' => 'commune-nom-min',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Nom de la commune en minuscules',
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
                'name' => 'alias',
                'type' => 'text',
                'attributes' => [
                    'id' => 'commune-alias',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Autre nom (en majuscules)',
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
                'name' => 'alias_min',
                'type' => 'text',
                'attributes' => [
                    'id' => 'commune-alias-min',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Autre nom (en minuscules)',
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
                'name' => 'aliasCG',
                'type' => 'text',
                'attributes' => [
                    'id' => 'commune-aliascg',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Nom CG',
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
                'name' => 'codePostal',
                'type' => 'SbmCommun\Form\Element\CodePostal',
                'attributes' => [
                    'id' => 'commune-codepostal',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Code postal',
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
                'name' => 'departement',
                'type' => 'text',
                'attributes' => [
                    'id' => 'commune-departement',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Code du département',
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
                'name' => 'canton',
                'type' => 'text',
                'attributes' => [
                    'id' => 'commune-canton',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Code du canton',
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
                'name' => 'population',
                'type' => 'text',
                'attributes' => [
                    'id' => 'commune-population',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Population',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'membre',
                'attributes' => [
                    'id' => 'commune-membre',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Commune membre',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'desservie',
                'attributes' => [
                    'id' => 'commune-desservie',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Commune desservie',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'visible',
                'attributes' => [
                    'id' => 'commune-visible',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Visible',
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
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Enregistrer',
                    'id' => 'commune-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'commune-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'communeId' => [
                'name' => 'communeId',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 5,
                            'max' => 6
                        ]
                    ]
                ]
            ],
            'nom' => [
                'name' => 'nom',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'StringToUpper'
                    ]
                ]
            ],
            'nom_min' => [
                'name' => 'nom_min',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'alias' => [
                'name' => 'alias',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'StringToUpper'
                    ]
                ]
            ],
            'alias_min' => [
                'name' => 'alias_min',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'aliasCG' => [
                'name' => 'aliasCG',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'codePostal' => [
                'name' => 'codePostal',
                'required' => true
            ],
            'departement' => [
                'name' => 'departement',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 2,
                            'max' => 3
                        ]
                    ]
                ]
            ],
            'canton' => [
                'name' => 'canton',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Digits'
                    ]
                ]
            ],
            'population' => [
                'name' => 'population',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Digits'
                    ]
                ]
            ]
        ];
    }

    public function modifFormForEdit()
    {
        $this->remove('communeId');
        $this->get('nom')->setAttribute('autofocus', 'autofocus');
        $this->add(
            [
                'name' => 'communeId',
                'type' => 'hidden'
            ]);
        $this->add(
            [
                'name' => 'communeInsee',
                'type' => 'text',
                'attributes' => [
                    'id' => 'commune-codeid',
                    'disabled' => 'disabled',
                    'class' => 'form commune codeid'
                ],
                'options' => [
                    'label' => 'Code INSEE de la commune',
                    'label_attributes' => [
                        'class' => 'form commune label label-codeid'
                    ],
                    'error_attributes' => [
                        'class' => 'form commune error error-codeid'
                    ]
                ]
            ]);
        return $this;
    }

    public function setData($data)
    {
        parent::setData($data);
        if ($this->has('communeInsee')) {
            $e = $this->get('communeInsee');
            $e->setValue($this->get('communeId')
                ->getValue());
        }
    }
}