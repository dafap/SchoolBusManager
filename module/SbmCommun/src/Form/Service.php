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
 * @date 27 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Form;

use SbmBase\Model\Session;
use Zend\InputFilter\InputFilterProviderInterface;

class Service extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     * Permet de faire savoir à getInputFilterSpecification() si on est en modif (true) ou
     * en ajout (false)
     *
     * @var bool
     */
    private $edit;

    public function __construct()
    {
        $this->edit = false;
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
        $this->add([
            'name' => 'millesime',
            'type' => 'hidden'
        ]);
        $this->add(
            [
                'name' => 'ligneId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'service-ligneid',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Ligne',
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
                'name' => 'sens',
                'type' => 'Zend\Form\Element\Radio',
                'attributes' => [
                    'id' => 'service-sens',
                    'class' => 'sbm-radio'
                ],
                'options' => [
                    'label' => 'Sens',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ],
                    'value_options' => [
                        '1' => 'Aller',
                        '2' => 'Retour'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'moment',
                'type' => 'Zend\Form\Element\Radio',
                'attributes' => [
                    'id' => 'service-moment',
                    'class' => 'sbm-radio'
                ],
                'options' => [
                    'label' => 'Moment',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ],
                    'value_options' => [
                        '1' => 'Matin',
                        '2' => 'Midi',
                        '3' => 'Soir'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'ordre',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'service-ordre',
                    'class' => 'sbm-width-10c',
                ],
                'options' => [
                    'label' => 'Ordre',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Numéro ?',
                    'value_options' => [
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                        '7' => '7',
                        '8' => '8',
                        '9' => '9',
                        '10' => '10',
                        '11' => '11',
                        '12' => '12',
                        '13' => '13',
                        '14' => '14',
                        '15' => '15',
                        '16' => '16'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'rang',
                'type' => 'SbmCommun\Form\Element\IsInt',
                'attributes' => [
                    'id' => 'service-rang',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Rang de recherche',
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
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Désignation',
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
                'name' => 'semaine',
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'attributes' => [
                    'id' => 'service-semaine',
                    'class' => 'sbm-multicheckbox'
                ],
                'options' => [
                    'use_hidden_element' => true,
                    'label' => 'Jours de circulation',
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
                'name' => 'nbPlaces',
                'type' => 'SbmCommun\Form\Element\IsInt',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'actif',
                'attributes' => [
                    'id' => 'service-actif',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Service actif',
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
                    'id' => 'service-visible',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Service visible',
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
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'commentaire',
                'attributes' => [
                    'id' => 'service-commentaire',
                    'class' => 'sbm-note'
                ],
                'options' => [
                    'label' => 'Commentaires',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm_error'
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
        $this->edit = true;
        $this->remove('ligneId')
            ->remove('sens')
            ->remove('moment')
            ->remove('ordre');

        $this->add([
            'name' => 'ligneId',
            'type' => 'hidden'
        ])
            ->add([
            'name' => 'sens',
            'type' => 'hidden'
        ])
            ->add([
            'name' => 'moment',
            'type' => 'hidden'
        ])
            ->add([
            'name' => 'ordre',
            'type' => 'hidden'
        ]);

        $this->add(
            [
                'name' => 'newligneId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'service-ligneid',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Ligne',
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
                'name' => 'newsens',
                'type' => 'Zend\Form\Element\Radio',
                'attributes' => [
                    'id' => 'service-sens',
                    'class' => 'sbm-radio'
                ],
                'options' => [
                    'label' => 'Sens',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ],
                    'value_options' => [
                        '1' => 'Aller',
                        '2' => 'Retour'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'newmoment',
                'type' => 'Zend\Form\Element\Radio',
                'attributes' => [
                    'id' => 'service-moment',
                    'class' => 'sbm-radio'
                ],
                'options' => [
                    'label' => 'Moment',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ],
                    'value_options' => [
                        '1' => 'Matin',
                        '2' => 'Midi',
                        '3' => 'Soir'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'newordre',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'service-ordre',
                    'class' => 'sbm-width-5c',
                    'autofocus' => 'autofocus'
                ],
                'options' => [
                    'label' => 'Ordre',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Numéro ?',
                    'value_options' => [
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                        '7' => '7',
                        '8' => '8',
                        '9' => '9',
                        '10' => '10',
                        '11' => '11',
                        '12' => '12',
                        '13' => '13',
                        '14' => '14',
                        '15' => '15',
                        '16' => '16'
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
        $spec = [
            'ligneId' => [
                'name' => 'ligneId',
                'required' => true
            ],
            'sens' => [
                'name' => 'sens',
                'required' => true
            ],
            'moment' => [
                'name' => 'moment',
                'required' => true
            ],
            'ordre' => [
                'name' => 'ordre',
                'required' => true
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
            'actif' => [
                'name' => 'actif',
                'required' => false
            ],
            'visible' => [
                'name' => 'visible',
                'required' => false
            ],
            'semaine' => [
                'name' => 'semaine',
                'required' => true
            ],
            'transporteurId' => [
                'name' => 'transporteurId',
                'required' => true
            ],
            'surEtatCG' => [
                'name' => 'surEtatCG',
                'required' => false
            ],
            'rang' => [
                'name' => 'rang',
                'required' => true
            ],
            'nbPlaces' => [
                'name' => 'nbPlaces',
                'required' => true
            ]
        ];
        if ($this->edit) {
            $spec = array_merge($spec,
                [
                    'newligneId' => [
                        'name' => 'newligneId',
                        'required' => true
                    ],
                    'newsens' => [
                        'name' => 'newsens',
                        'required' => true
                    ],
                    'newmoment' => [
                        'name' => 'newmoment',
                        'required' => true
                    ],
                    'newordre' => [
                        'name' => 'newordre',
                        'required' => true
                    ]
                ]);
        }
        return $spec;
    }

    public function setData($data)
    {
        if (! array_key_exists('millesime', $data)) {
            $data['millesime'] = Session::get('millesime');
        }
        parent::setData($data);
        if ($this->has('newligneId')) {
            $e = $this->get('newligneId');
            $e->setValue($this->get('ligneId')
                ->getValue());
        }
        if ($this->has('newsens')) {
            $e = $this->get('newsens');
            $e->setValue($this->get('sens')
                ->getValue());
        }
        if ($this->has('newmoment')) {
            $e = $this->get('newmoment');
            $e->setValue($this->get('moment')
                ->getValue());
        }
        if ($this->has('newordre')) {
            $e = $this->get('newordre');
            $e->setValue($this->get('ordre')
                ->getValue());
        }
    }
}