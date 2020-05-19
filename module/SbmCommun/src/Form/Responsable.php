<?php
/**
 * Formulaire de saisie et modification d'un responsable
 *
 * A noter que les éléments SbmCommun\Form\Element\NomPropre et
 * SbmCommun\Form\Element\Prenom ont leur propre méthode getInputSpecification()
 *
 * @project sbm
 * @package module/SbmCommun/src/Form
 * @filesource Responsable.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Form;

use SbmBase\Model\StdLib;
use Zend\InputFilter\InputFilterProviderInterface;

class Responsable extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     * Indicateur
     *
     * @var bool
     */
    private $verrouille;

    /**
     * Indicateur
     *
     * @var bool
     */
    private $hassbmservicesms;

    /**
     * Constructeur
     *
     * @param boolean $options
     *            indique si l'identité doit être verrouillée en lecture seule
     */
    public function __construct($name = null, $options = [])
    {
        $this->verrouille = StdLib::getParam('verrouille', $options, false);
        unset($options['verrouille']);
        $this->hassbmservicesms = StdLib::getParam('hassbmservicesms', $options, false);
        unset($options['hassbmservicesms']);
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');
        $this->init();
    }

    public function init()
    {
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
            'name' => 'responsableId',
            'type' => 'hidden'
        ]);
        $this->add([
            'name' => 'userId',
            'type' => 'hidden'
        ]);
        $this->add([
            'name' => 'nature',
            'type' => 'hidden'
        ]);
        if ($this->verrouille) {
            $this->add([
                'name' => 'titre',
                'type' => 'hidden'
            ]);
        } else {
            $this->add(
                [
                    'name' => 'titre',
                    'type' => 'Zend\Form\Element\Select',
                    'attributes' => [
                        'id' => 'responsable-titre',
                        'class' => 'sbm-width-15c'
                    ],
                    'options' => [
                        'label' => 'Identité du responsable',
                        'label_attributes' => [
                            'class' => 'sbm-label responsable-titre'
                        ],
                        'value_options' => [
                            'M.' => 'Monsieur',
                            'Mme' => 'Madame',
                            'Mlle' => 'Mademoiselle',
                            'Dr' => 'Docteur',
                            'Me' => 'Maître',
                            'Pr' => 'Professeur',
                            'Asso' => 'Association'
                        ],
                        'empty_option' => 'Choisissez la civilité',
                        'error_attributes' => [
                            'class' => 'sbm-error'
                        ]
                    ]
                ]);
        }
        $this->add(
            [
                'name' => 'nom',
                'type' => 'SbmCommun\Form\Element\NomPropre',
                'attributes' => [
                    'id' => 'responsable-nom',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Nom',
                    'label_attributes' => [
                        'class' => 'sbm-label responsable-nom'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'prenom',
                'type' => 'SbmCommun\Form\Element\Prenom',
                'attributes' => [
                    'id' => 'responsable-prenom',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Prénom',
                    'label_attributes' => [
                        'class' => 'sbm-label responsable-prenom'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'titre2',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'responsable-titre2',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Conjoint',
                    'label_attributes' => [
                        'class' => 'sbm-label help'
                    ],
                    'value_options' => [
                        'M.' => 'Monsieur',
                        'Mme' => 'Madame',
                        'Mlle' => 'Mademoiselle',
                        'Dr' => 'Docteur',
                        'Me' => 'Maître',
                        'Pr' => 'Professeur'
                    ],
                    'empty_option' => 'Choisissez la civilité',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'nom2',
                'type' => 'SbmCommun\Form\Element\NomPropre',
                'attributes' => [
                    'id' => 'responsable-nom2',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Nom',
                    'label_attributes' => [
                        'class' => 'sbm-label responsable-nom'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'prenom2',
                'type' => 'SbmCommun\Form\Element\Prenom',
                'attributes' => [
                    'id' => 'responsable-prenom2',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Prénom',
                    'label_attributes' => [
                        'class' => 'sbm-label responsable-prenom'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'adresseL1',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'responsable-adresseL1',
                    'class' => 'sbm-width-40c'
                ],
                'options' => [
                    'label' => 'Adresse',
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
                'name' => 'adresseL2',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'responsable-adresseL2',
                    'class' => 'sbm-width-40c'
                ],
                'options' => [
                    'label' => 'Complément d\'adresse',
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
                'name' => 'adresseL3',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'responsable-adresseL3',
                    'class' => 'sbm-width-40c'
                ],
                'options' => [
                    'label' => 'Complément d\'adresse',
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
                    'id' => 'responsable-codePostal',
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
                'name' => 'communeId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'responsable-communeId',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Commune',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez une commune',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->get('communeId')->setDisableInArrayValidator(true);
        $this->add(
            [
                'name' => 'telephoneF',
                'type' => 'SbmCommun\Form\Element\Telephone',
                'attributes' => [
                    'id' => 'respondable-telephoneF',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Téléphone',
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
                'name' => 'telephoneP',
                'type' => 'SbmCommun\Form\Element\Telephone',
                'attributes' => [
                    'id' => 'respondable-telephoneP',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Autre téléphone',
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
                'name' => 'telephoneT',
                'type' => 'SbmCommun\Form\Element\Telephone',
                'attributes' => [
                    'id' => 'respondable-telephoneT',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Autre téléphone',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        if ($this->hassbmservicesms) {
            $this->add(
                [
                    'name' => 'smsF',
                    'type' => 'Zend\Form\Element\Radio',
                    'attributtes' => [
                        'class' => 'sbm-radio'
                    ],
                    'options' => [
                        'label' => 'Accepte les SMS',
                        'label_attributes' => [
                            'class' => 'sbm-label-radio'
                        ],
                        'value_options' => [
                            '1' => 'Oui',
                            '0' => 'Non'
                        ]
                    ]
                ]);
            $this->add(
                [
                    'name' => 'smsP',
                    'type' => 'Zend\Form\Element\Radio',
                    'attributtes' => [
                        'class' => 'sbm-radio'
                    ],
                    'options' => [
                        'label' => 'Accepte les SMS',
                        'label_attributes' => [
                            'class' => 'sbm-label-radio'
                        ],
                        'value_options' => [
                            '1' => 'Oui',
                            '0' => 'Non'
                        ]
                    ]
                ]);
            $this->add(
                [
                    'name' => 'smsT',
                    'type' => 'Zend\Form\Element\Radio',
                    'attributtes' => [
                        'class' => 'sbm-radio'
                    ],
                    'options' => [
                        'label' => 'Accepte les SMS',
                        'label_attributes' => [
                            'class' => 'sbm-label-radio'
                        ],
                        'value_options' => [
                            '1' => 'Oui',
                            '0' => 'Non'
                        ]
                    ]
                ]);
        }
        $this->add(
            [
                'name' => 'email',
                'type' => 'Zend\Form\Element\Email',
                'attributes' => [
                    'id' => 'respondable-email',
                    'class' => 'sbm-width-50c'
                ],
                'options' => [
                    'label' => 'Email',
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
                'name' => 'ancienAdresseL1',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'responsable-ancienAdresseL1',
                    'class' => 'sbm-width-40c'
                ],
                'options' => [
                    'label' => 'Ancienne adresse',
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
                'name' => 'ancienAdresseL2',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'responsable-ancienAdresseL2',
                    'class' => 'sbm-width-40c'
                ],
                'options' => [
                    'label' => 'Ancienne adresse',
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
                'name' => 'ancienAdresseL3',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'responsable-ancienAdresseL3',
                    'class' => 'sbm-width-40c'
                ],
                'options' => [
                    'label' => 'Ancienne adresse',
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
                'name' => 'ancienCodePostal',
                'type' => 'SbmCommun\Form\Element\CodePostal',
                'attributes' => [
                    'id' => 'responsable-ancienCodePostal',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Ancien code postal',
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
                'name' => 'ancienCommuneId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'responsable-ancienCommuneId',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Ancienne commune',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez une commune',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'demenagement',
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => [
                    'id' => 'responsable-demenagement'
                ],
                'options' => [
                    'label' => 'Déménagement',
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
                'name' => 'dateDemenagement',
                'type' => 'Zend\Form\Element\Date',
                'attributes' => [
                    'id' => 'responsable-dateDemenagement'
                ],
                'options' => [
                    'label' => 'Date du déménagement',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'format' => 'Y-m-d'
                ]
            ]);
        $this->add(
            [
                'name' => 'selection',
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => [
                    'id' => 'responsable-selection'
                ],
                'options' => [
                    'label' => 'Sélectionné',
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
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'note',
                'attributes' => [
                    'id' => 'responsable-note'
                ],
                'options' => [
                    'label' => 'Notes',
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
                    'id' => 'responsable-submit',
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
                    'id' => 'responsable-cancel',
                    'class' => 'button default cancel'
                ]
            ]);

        if ($this->verrouille) {
            $this->verrouilleIdentity();
        }
    }

    public function getInputFilterSpecification()
    {
        $spec = [
            'titre2' => [
                'name' => 'titre2',
                'required' => false
            ],
            'nom2' => [
                'name' => 'nom2',
                'required' => false
            ],
            'prenom2' => [
                'name' => 'prenom2',
                'required' => false
            ],
            'adresseL1' => [
                'name' => 'adresseL1',
                'required' => true
            ],
            'adresseL2' => [
                'name' => 'adresseL2',
                'required' => false
            ],
            'adresseL3' => [
                'name' => 'adresseL3',
                'required' => false
            ],
            'codePostal' => [
                'name' => 'codePostal',
                'required' => true
            ],
            'communeId' => [
                'name' => 'communeId',
                'required' => true
            ],
            'ancienAdresseL1' => [
                'name' => 'ancienAdresseL1',
                'required' => false
            ],
            'ancienAdresseL2' => [
                'name' => 'ancienAdresseL2',
                'required' => false
            ],
            'ancienAdresseL3' => [
                'name' => 'ancienAdresseL3',
                'required' => false
            ],
            'ancienCommuneId' => [
                'name' => 'ancienCommuneId',
                'required' => false
            ],
            'ancienCodePostal' => [
                'name' => 'ancienCodePostal',
                'required' => false
            ],
            'telephoneF' => [
                'name' => 'telephoneF',
                'required' => false
            ],
            'telephoneP' => [
                'name' => 'telephoneP',
                'required' => false
            ],
            'telephoneT' => [
                'name' => 'telephoneT',
                'required' => false
            ],
            'email' => [
                'name' => 'email',
                'required' => false
            ],
            'dateDemenagement' => [
                'name' => 'dateDemenagement',
                'required' => false
            ],
            'selection' => [
                'name' => 'selection',
                'required' => false
            ]
        ];

        if ($this->hassbmservicesms) {
            $spec['smsF'] = [
                'name' => 'smsF',
                'required' => false
            ];
            $spec['smsP'] = [
                'name' => 'smsP',
                'required' => false
            ];
            $spec['smsT'] = [
                'name' => 'smsT',
                'required' => false
            ];
        }

        if ($this->verrouille) {
            $spec['titre'] = [
                'name' => 'adresseL1',
                'required' => false
            ];
            $spec['titre'] = [
                'name' => 'codePostal',
                'required' => false
            ];
            $spec['titre'] = [
                'name' => 'titre',
                'required' => false
            ];
            $spec['demenagement'] = [
                'name' => 'demenagement',
                'required' => false
            ];
        }
        return $spec;
    }

    private function verrouilleIdentity()
    {
        foreach ([
            'nom' => 'readonly',
            'prenom' => 'readonly',
            'email' => 'readonly'
        ] as $elementName => $attr) {
            $e = $this->get($elementName);
            $e->setAttribute($attr, $attr);
        }
    }

    public function isValid()
    {
        $result = parent::isValid();
        // un des 3 numéros de téléphones doit être renseigné
        if (empty($this->data['telephoneF']) && empty($this->data['telephoneP']) &&
            empty($this->data['telephoneT'])) {
            $result = false;
            $element = $this->get('telephoneT');
            $element->setMessages(
                [
                    'Vous devez indiquer au moins un numéro de téléphone où l\'on pourra vous joindre.'
                ]);
        } elseif ($this->hassbmservicesms) {
            // si un numéro est renseigné, on doit dire s'il peut recevoir des SMS
            if (! empty($this->data['telephoneF']) && ! isset($this->data['smsF'])) {
                $result = false;
                $element = $this->get('telephoneF');
                $element->setMessages(
                    [
                        'Vous devez indiquer si le responsable accepte de recevoir des SMS sur ce numéro'
                    ]);
            }
            if (! empty($this->data['telephoneP']) && ! isset($this->data['smsP'])) {
                $result = false;
                $element = $this->get('telephoneP');
                $element->setMessages(
                    [
                        'Vous devez indiquer si le responsable accepte de recevoir des SMS sur ce numéro'
                    ]);
            }
            if (! empty($this->data['telephoneT']) && ! isset($this->data['smsT'])) {
                $result = false;
                $element = $this->get('telephoneT');
                $element->setMessages(
                    [
                        'Vous devez indiquer si le responsable accepte de recevoir des SMS sur ce numéro'
                    ]);
            }
        }
        return $result;
    }

}