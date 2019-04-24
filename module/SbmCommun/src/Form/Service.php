<?php
/**
 * Formulaire de saisie et modification d'une service
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Service.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Service extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('service');
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
                'name' => 'serviceId',
                'type' => 'text',
                'attributes' => [
                    'id' => 'service-codeid',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Code du service',
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
                    'id' => 'service-alias',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Rabattement sur',
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
                'name' => 'aliasTr',
                'type' => 'text',
                'attributes' => [
                    'id' => 'service-aliasTr',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Identifiant sur le véhicule',
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
                    'id' => 'service-aliascg',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Désignation au CR',
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
                'name' => 'lotId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'service-lotId',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Lot du marché',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un lot de marché',
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
                    'id' => 'service-nom',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Désignation du service',
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
                'name' => 'horaire1',
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'attributes' => [
                    'id' => 'circuit-semaine',
                    'class' => 'sbm-multicheckbox'
                ],
                'options' => [
                    'use_hidden_element' => true,
                    'label' => 'Jours de l\'horaire 1',
                    'label_attributes' => [
                        'class' => 'sbm-label-semaine'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);

        $this->add(
            [
                'name' => 'horaire2',
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'attributes' => [
                    'id' => 'circuit-semaine',
                    'class' => 'sbm-multicheckbox'
                ],
                'options' => [
                    'use_hidden_element' => true,
                    'label' => 'Jours de l\'horaire 2',
                    'label_attributes' => [
                        'class' => 'sbm-label-semaine'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);

        $this->add(
            [
                'name' => 'horaire3',
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'attributes' => [
                    'id' => 'circuit-semaine',
                    'class' => 'sbm-multicheckbox'
                ],
                'options' => [
                    'use_hidden_element' => true,
                    'label' => 'Jours de l\'horaire 3',
                    'label_attributes' => [
                        'class' => 'sbm-label-semaine'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'transporteurId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'service-transporteurId',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Transporteur',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un transporteur',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'operateur',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'service-operateur',
                    'class' => 'sbm-width-20c'
                ],
                'options' => [
                    'label' => 'Opérateur',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un opérateur',
                    'value_options' => [],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'nbPlaces',
                'type' => 'text',
                'attributes' => [
                    'id' => 'service-nbPlaces',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Nombre de places',
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
                'name' => 'kmAVide',
                'type' => 'SbmCommun\Form\Element\IsDecimal',
                'attributes' => [
                    'id' => 'service-kmAVide',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Km à vide',
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
                'name' => 'kmEnCharge',
                'type' => 'SbmCommun\Form\Element\IsDecimal',
                'attributes' => [
                    'id' => 'service-kmEnCharge',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Km en charge',
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
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'name' => 'natureCarte',
                'attributes' => [
                    'id' => 'service-natureCarte',
                    'class' => 'sbm-multicheckbox'
                ],
                'options' => [
                    'label' => 'Indiquer la nature des cartes à imprimer',
                    'label_attributes' => [
                        'class' => 'sbm-label-nature-carte'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'surEtatCG',
                'attributes' => [
                    'id' => 'service-surEtatCG',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sur les états du CR',
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
                    'id' => 'service-submit',
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
                    'id' => 'service-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function modifFormForEdit()
    {
        $this->remove('serviceId');
        $this->add([
            'name' => 'serviceId',
            'type' => 'hidden'
        ]);
        $this->get('nom')->setAttribute('autofocus', 'autofocus');
        $this->add(
            [
                'name' => 'codeService',
                'type' => 'text',
                'attributes' => [
                    'id' => 'service-codeid',
                    'disabled' => 'disabled',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Code du service',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        return $this;
    }

    public function getInputFilterSpecification()
    {
        return [
            'serviceId' => [
                'name' => 'serviceId',
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
                        'name' => 'SbmCommun\Model\Validator\CodeService'
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
                    ]
                ]
            ],
            'aliasTr' => [
                'name' => 'aliasTr',
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
            'nom' => [
                'name' => 'nom',
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
            'horaire1' => [
                'name' => 'horaire1',
                'required' => true
            ],
            'horaire2' => [
                'name' => 'horaire2',
                'required' => false
            ],
            'horaire3' => [
                'name' => 'horaire3',
                'required' => false
            ],
            'transporteurId' => [
                'name' => 'transporteurId',
                'required' => true
            ],
            'surEtatCG' => [
                'name' => 'surEtatCG',
                'required' => false
            ],
            'nbPlaces' => [
                'name' => 'nbPlaces',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'Digits'
                    ]
                ]
            ]
        ];
    }

    public function setData($data)
    {
        parent::setData($data);
        if ($this->has('codeService')) {
            $e = $this->get('codeService');
            $e->setValue($this->get('serviceId')
                ->getValue());
        }
    }
}