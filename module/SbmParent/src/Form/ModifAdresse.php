<?php
/**
 * Formulaire de modification de l'adresse d'un responsable
 *
 * La méthode valid() vérifie si l'adresse a changé et dans ce cas place
 * l'ancienne adresse dans les data du formulaire.
 *
 * @project sbm
 * @package SbmParent/src/Form
 * @filesource ModifAdresse.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmParent\Form;

use SbmBase\Model\StdLib;
use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class ModifAdresse extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     * Indicateur
     *
     * @var bool
     */
    private $hassbmservicesms;

    public function __construct($name = null, $options = [])
    {
        $this->hassbmservicesms = StdLib::getParam('hassbmservicesms', $options, false);
        unset($options['hassbmservicesms']);
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');
        $this->init();
    }

    public function init()
    {
        $this->add([
            'type' => 'hidden',
            'name' => 'responsableId'
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
                'name' => 'adresseL1',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'adresseL1',
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
                    'id' => 'adresseL2',
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
                'name' => 'adresseL3',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'adresseL3',
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
                    'disable_inarray_validator' => true,
                    'allow_empty' => false,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'telephoneF',
                'type' => 'SbmCommun\Form\Element\Telephone',
                'attributes' => [
                    'id' => 'telephoneF',
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
                    'id' => 'telephoneF',
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
                    'id' => 'telephoneF',
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
                    'attributes' => [
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
                    'attributes' => [
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
                    'attributes' => [
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
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Enregistrer les modifications',
                    'id' => 'responsable-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'button default submit left-95px'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'responsable-cancel',
                    'class' => 'button default cancel left-10px'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
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
            'telephoneF' => [
                'name' => 'telephoneF',
                'required' => false
            ],
            'telephoneP' => [
                'name' => 'telephoneF',
                'required' => false
            ],
            'telephoneT' => [
                'name' => 'telephoneF',
                'required' => false
            ],
            'smsF' => [
                'name' => 'smsF',
                'required' => false
            ],
            'smsP' => [
                'name' => 'smsP',
                'required' => false
            ],
            'smsT' => [
                'name' => 'smsT',
                'required' => false
            ]
        ];
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