<?php
/**
 * Formulaire de saisie et modification d'un circuit
 *
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Circuit.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Circuit extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('circuit');
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'name' => 'circuitId',
                'type' => 'hidden'
            ]);
        $this->add(
            [
                'name' => 'millesime',
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
                'name' => 'serviceId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'circuit-serviceId',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-55c'
                ],
                'options' => [
                    'label' => 'Service',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Quel service ?',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'stationId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'circuit-stationId',
                    'class' => 'sbm-width-55c'
                ],
                'options' => [
                    'label' => 'Point d\'arrêt',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Quel point d\'arrêt ?',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'passage',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'circuit-passage',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Passage',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'value_options' => [
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4'
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
                    'id' => 'circuit-semaine',
                    'class' => 'sbm-multicheckbox'
                ],
                'options' => [
                    'label' => 'Jours de passage',
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
                'name' => 'm1',
                'type' => 'SbmCommun\Form\Element\Time',
                'attributes' => [
                    'id' => 'circuit-m1',
                    'title' => 'Lundi, mardi, jeudi, vendredi. Format hh:mm',
                    'class' => 'sbm-width-10c',
                    'min' => '00:00',
                    'max' => '29:59',
                    'step' => '60'
                ],
                'options' => [
                    'format' => 'H:i',
                    'label' => 'Lu Ma Je Ve',
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
                'name' => 's1',
                'type' => 'SbmCommun\Form\Element\Time',
                'attributes' => [
                    'id' => 'circuit-s1',
                    'title' => 'Lundi, mardi, jeudi, vendredi. Format hh:mm',
                    'class' => 'sbm-width-10c',
                    'min' => '00:00',
                    'max' => '29:59',
                    'step' => '60'
                ],
                'options' => [
                    'format' => 'H:i',
                    'label' => 'Lu Ma Je Ve',
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
                'name' => 'm2',
                'type' => 'SbmCommun\Form\Element\Time',
                'attributes' => [
                    'id' => 'circuit-m2',
                    'title' => 'Format hh:mm',
                    'class' => 'sbm-width-10c',
                    'min' => '00:00',
                    'max' => '29:59',
                    'step' => '60'
                ],
                'options' => [
                    'format' => 'H:i',
                    'label' => 'Mercredi',
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
                'name' => 's2',
                'type' => 'SbmCommun\Form\Element\Time',
                'attributes' => [
                    'id' => 'circuit-s2',
                    'title' => 'Format hh:mm',
                    'class' => 'sbm-width-10c',
                    'min' => '00:00',
                    'max' => '29:59',
                    'step' => '60'
                ],
                'options' => [
                    'format' => 'H:i',
                    'label' => 'Mercredi',
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
                'name' => 'm3',
                'type' => 'SbmCommun\Form\Element\Time',
                'attributes' => [
                    'id' => 'circuit-m3',
                    'title' => 'Format hh:mm',
                    'class' => 'sbm-width-10c',
                    'min' => '00:00',
                    'max' => '29:59',
                    'step' => '60'
                ],
                'options' => [
                    'format' => 'H:i',
                    'label' => 'Samedi',
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
                'name' => 's3',
                'type' => 'SbmCommun\Form\Element\Time',
                'attributes' => [
                    'id' => 'circuit-s3',
                    'title' => 'Format hh:mm',
                    'class' => 'sbm-width-10c',
                    'min' => '00:00',
                    'max' => '29:59',
                    'step' => '60'
                ],
                'options' => [
                    'format' => 'H:i',
                    'label' => 'Samedi',
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
                'name' => 'distance',
                'type' => 'text',
                'attributes' => [
                    'id' => 'circuit-distance',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Distance',
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
                'name' => 'montee',
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => [
                    'id' => 'circuit-montee',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Point de montée',
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
                'name' => 'descente',
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => [
                    'id' => 'circuit-descente',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Point de descente',
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
                'name' => 'typeArret',
                'type' => 'text',
                'attributes' => [
                    'id' => 'circuit-typeArret',
                    'class' => 'sbm-width-55c'
                ],
                'options' => [
                    'label' => 'Type d\'arrêt',
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
                'name' => 'commentaire1',
                'type' => 'textarea',
                'attributes' => [
                    'id' => 'circuit-commentaire1',
                    'class' => 'sbm-width-40c'
                ],
                'options' => [
                    'label' => 'Commentaire aller',
                    'label_attributes' => [
                        'class' => 'sbm-label-top'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'commentaire2',
                'type' => 'textarea',
                'attributes' => [
                    'id' => 'circuit-commentaire2',
                    'class' => 'sbm-width-40c'
                ],
                'options' => [
                    'label' => 'Commentaire retour',
                    'label_attributes' => [
                        'class' => 'sbm-label-top'
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
                    'id' => 'station-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'station-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'serviceId' => [
                'name' => 'serviceId',
                'required' => true
            ],
            'stationId' => [
                'name' => 'stationId',
                'required' => true
            ],
            'semaine' => [
                'name' => 'semaine',
                'required' => true
            ],
            'm1' => [
                'name' => 'm1',
                'required' => false
            ],
            's1' => [
                'name' => 's1',
                'required' => false
            ],
            'm2' => [
                'name' => 'm2',
                'required' => false
            ],
            's2' => [
                'name' => 's2',
                'required' => false
            ],
            'm3' => [
                'name' => 'm3',
                'required' => false
            ],
            's3' => [
                'name' => 's3',
                'required' => false
            ],
            'distance' => [
                'name' => 'distance',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'SbmCommun\Model\Validator\Decimal'
                    ]
                ]
            ],
            'typeArret' => [
                'name' => 'typeArret',
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
            'commentaire' => [
                'name' => 'commentaire',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ]
        ];
    }

    public function setData($data)
    {
        // adapte le format des time pour les éléments DateTimeLocal du formulaire
        $elementsTime = [
            'm1',
            'm2',
            'm3',
            's1',
            's2',
            's3'
        ];
        for ($i = 0; $i < count($elementsTime); $i ++) {
            if (isset($data[$elementsTime[$i]])) {
                $dte = new \DateTime($data[$elementsTime[$i]]);
                $data[$elementsTime[$i]] = $dte->format('H:i');
            }
        }
        // appelle la méthode de ZF2
        parent::setData($data);
    }

    public function setValueOptions($element, array $values_options)
    {
        if ($element == 'semaine') {
            $values_options_semaine = [];
            foreach ($values_options as $key => $value) {
                $values_options_semaine[] = [
                    'value' => $key,
                    'label' => $value,
                    'attributes' => [
                        'id' => 'semaine-' . $value
                    ]
                ];
            }
            $values_options = $values_options_semaine;
        }
        return parent::setValueOptions($element, $values_options);
    }
}