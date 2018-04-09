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
 * @date 4 avr. 2018
 * @version 2018-2.4.0
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
                'name' => 'aliasCG',
                'type' => 'text',
                'attributes' => [
                    'id' => 'service-aliascg',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Désignation au CG',
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
                'type' => 'text',
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
                'type' => 'text',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'surEtatCG',
                'attributes' => [
                    'id' => 'service-surEtatCG',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sur les états du CG',
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
        $e = $this->remove('serviceId');
        $this->add(
            [
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