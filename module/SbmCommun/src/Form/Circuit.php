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
 * @date 29 fév. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Form;

use SbmBase\Model\Session;
use Zend\InputFilter\InputFilterProviderInterface;

class Circuit extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\Horaires
     */
    private $horaires;

    public function __construct()
    {
        parent::__construct('circuit');
        $this->setAttribute('method', 'post');
        $this->add([
            'name' => 'circuitId',
            'type' => 'hidden'
        ]);
        $this->add([
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
                'name' => 'ligneId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'circuit-ligneId',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-55c'
                ],
                'options' => [
                    'label' => 'Ligne',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Quelle ligne ?',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'sens',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'circuit-sens',
                    'class' => 'sbm-width-10c',
                    'autofocus' => 'autofocus'
                ],
                'options' => [
                    'label' => 'Sens',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Quel sens ?',
                    'value_options' => [
                        '1' => 'Aller',
                        '2' => 'Retour'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'moment',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'circuit-moment',
                    'class' => 'sbm-cidth-10c',
                    'autofocus' => 'autofocus'
                ],
                'options' => [
                    'label' => 'Moment',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'A quel moment ?',
                    'value_options' => [
                        '1' => 'Mation',
                        '2' => 'Midi',
                        '3' => 'Soir'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'ordre',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'circuit-ordre',
                    'class' => 'sbm-width-10c',
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
                'name' => 'ouvert',
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => [
                    'id' => 'circuit-ouvert',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Station ouverte',
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
                'name' => 'visible',
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => [
                    'id' => 'circuit-visible',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Visible par les parents',
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
                'name' => 'horaireA',
                'type' => 'SbmCommun\Form\Element\Time',
                'attributes' => [
                    'id' => 'circuit-horaireA',
                    'title' => 'Format hh:mm',
                    'class' => 'horaire horaireA',
                    'min' => '00:00',
                    'max' => '29:59',
                    'step' => '60'
                ],
                'options' => [
                    'format' => 'H:i',
                    'label' => '',
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
                'name' => 'horaireD',
                'type' => 'SbmCommun\Form\Element\Time',
                'attributes' => [
                    'id' => 'circuit-horaireD',
                    'title' => 'Format hh:mm',
                    'class' => 'horaire horaireD',
                    'min' => '00:00',
                    'max' => '29:59',
                    'step' => '60'
                ],
                'options' => [
                    'format' => 'H:i',
                    'label' => '',
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
                'type' => 'SbmCommun\Form\Element\IsDecimal',
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
                'name' => 'correspondance',
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => [
                    'id' => 'circuit-correspondance',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Point de correspondance',
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
                'name' => 'emplacement',
                'type' => 'text',
                'attributes' => [
                    'id' => 'circuit-emplacement',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Emplacement',
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
            'stationId' => [
                'name' => 'stationId',
                'required' => true
            ],
            'semaine' => [
                'name' => 'semaine',
                'required' => true
            ],
            'horaireA' => [
                'name' => 'horaireA',
                'required' => false
            ],
            'horaireD' => [
                'name' => 'horaireD',
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
            'emplacement' => [
                'name' => 'emplacement',
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
        // adapte le format des time pour les éléments Time du formulaire
        $elementsTime = [
            'horaireA',
            'horaireD'
        ];
        for ($i = 0; $i < count($elementsTime); $i ++) {
            if (! empty($data[$elementsTime[$i]])) {
                $dte = new \DateTime($data[$elementsTime[$i]]);
                $data[$elementsTime[$i]] = $dte->format('H:i');
            }
        }
        if (! array_key_exists('millesime', $data)) {
            $data['millesime'] = Session::get('millesime');
        }
        // appelle la méthode de ZF2
        parent::setData($data);
    }

    public function setValueOptions($element, array $values_options)
    {
        if ($element == 'semaine') {
            $values_options_semaine = [];
            foreach ($values_options as $key => $value) {
                for ($i = 0; $i < $key; $i ++) {
                    if ($key >> $i == 1) {
                        $i ++;
                        break;
                    }
                }
                $values_options_semaine[] = [
                    'value' => $key,
                    'label' => $value,
                    'attributes' => [
                        'id' => 'jours-horaire' . $i
                    ],
                    'label_attributes' => [
                        'id' => 'label-jours-' . $key
                    ]
                ];
            }
            $values_options = $values_options_semaine;
        }
        return parent::setValueOptions($element, $values_options);
    }

    public function setLabelElement(string $elementName, string $label)
    {
        if ($this->has($elementName)) {
            $element = $this->get($elementName);
            $element->setLabel($label);
        } else {
            $msg = "Il n'y a pas d'élément du nom de `$elementName` dans ce formulaire.";
            throw new \LogicException($msg);
        }
    }

    public function setHoraires($horaires)
    {
        $this->horaires = $horaires;
    }
}