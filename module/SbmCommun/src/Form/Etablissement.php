<?php
/**
 * Formulaire de saisie et modification d'un etablissement
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Etablissement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Etablissement extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('etablissement');
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
                'name' => 'etablissementId',
                'type' => 'text',
                'attributes' => [
                    'id' => 'etablissement-codeid',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-10c'
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
                'name' => 'nom',
                'type' => 'text',
                'attributes' => [
                    'id' => 'etablissement-nom',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Nom',
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
                    'id' => 'etablissement-alias',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Autre désignation',
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
                    'id' => 'etablissement-aliasCG',
                    'class' => 'sbm-width-50c'
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
                'name' => 'adresse1',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'etablissement-adresse1',
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
                'name' => 'adresse2',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'etablissement-adresse2',
                    'class' => 'sbm-width-40c'
                ],
                'options' => [
                    'label' => 'Adresse (suite]',
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
                    'id' => 'etablissement-codepostal',
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
                    'id' => 'etablissement-communeId',
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
        $this->add(
            [
                'name' => 'directeur',
                'type' => 'text',
                'attributes' => [
                    'id' => 'etablissement-directeur',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Nom du directeur',
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
                'name' => 'telephone',
                'type' => 'SbmCommun\Form\Element\Telephone',
                'attributes' => [
                    'id' => 'etablissement-telephone',
                    'class' => 'sbm-width-10c'
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
                'name' => 'fax',
                'type' => 'SbmCommun\Form\Element\Telephone',
                'attributes' => [
                    'id' => 'etablissement-fax',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Fax',
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
                'name' => 'email',
                'type' => 'Zend\Form\Element\Email',
                'attributes' => [
                    'id' => 'etablissement-email',
                    'class' => 'sbm-width-45c'
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
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'name' => 'niveau',
                'attributes' => [
                    'id' => 'etablissement-niveau',
                    'class' => 'sbm-multicheckbox'
                ],
                'options' => [
                    'label' => 'Niveau',
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
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'statut',
                'attributes' => [
                    'id' => 'etablissement-statut',
                    'class' => 'sbm-radio'
                ],
                'options' => [
                    'label' => 'Statut',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ],
                    'value_options' => [
                        '0' => 'Privé',
                        '1' => 'Public'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'hMatin',
                'type' => 'SbmCommun\Form\Element\Time',
                'attributes' => [
                    'id' => 'etablissement-hMatin',
                    'title' => 'Format hh:mm',
                    'class' => 'sbm-width-10c',
                    'min' => '00:00',
                    'max' => '29:59',
                    'step' => '60'
                ],
                'options' => [
                    'format' => 'H:i',
                    'label' => 'Entrée',
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
                'name' => 'hMidi',
                'type' => 'SbmCommun\Form\Element\Time',
                'attributes' => [
                    'id' => 'etablissement-hMidi',
                    'title' => 'Format hh:mm',
                    'class' => 'sbm-width-10c',
                    'min' => '00:00',
                    'max' => '29:59',
                    'step' => '60'
                ],
                'options' => [
                    'format' => 'H:i',
                    'label' => 'Sortie',
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
                'name' => 'hAMidi',
                'type' => 'SbmCommun\Form\Element\Time',
                'attributes' => [
                    'id' => 'etablissement-hAMidi',
                    'title' => 'Format hh:mm',
                    'class' => 'sbm-width-10c',
                    'min' => '00:00',
                    'max' => '29:59',
                    'step' => '60'
                ],
                'options' => [
                    'format' => 'H:i',
                    'label' => 'Entrée',
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
                'name' => 'hSoir',
                'type' => 'SbmCommun\Form\Element\Time',
                'attributes' => [
                    'id' => 'etablissement-hSoir',
                    'title' => 'Format hh:mm',
                    'class' => 'sbm-width-10c',
                    'min' => '00:00',
                    'max' => '29:59',
                    'step' => '60'
                ],
                'options' => [
                    'format' => 'H:i',
                    'label' => 'Sortie',
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
                'name' => 'hGarderieOMatin',
                'type' => 'SbmCommun\Form\Element\Time',
                'attributes' => [
                    'id' => 'etablissement-hMatin',
                    'title' => 'Format hh:mm',
                    'class' => 'sbm-width-10c',
                    'min' => '00:00',
                    'max' => '29:59',
                    'step' => '60'
                ],
                'options' => [
                    'format' => 'H:i',
                    'label' => 'Tous les jours',
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
                'name' => 'hGarderieFSoir',
                'type' => 'SbmCommun\Form\Element\Time',
                'attributes' => [
                    'id' => 'etablissement-hMidi',
                    'title' => 'Format hh:mm',
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
                'name' => 'hGarderieFMidi',
                'type' => 'SbmCommun\Form\Element\Time',
                'attributes' => [
                    'id' => 'etablissement-hAMidi',
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
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'name' => 'jOuverture',
                'attributes' => [
                    'id' => 'etablissement-jOuverture',
                    'class' => 'sbm-multicheckbox'
                ],
                'options' => [
                    'label' => 'Jours d\'ouverture',
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
                'name' => 'regrPeda',
                'attributes' => [
                    'id' => 'etablissement-regrPeda',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Regroupement pédagogique',
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
                'name' => 'rattacheA',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'etablissement-rattacheA',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Secteur scolaire',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un établissement',
                    'allow_empty' => true,
                    'disable_inarray_validator' => false,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'x',
                'type' => 'text',
                'attributes' => [
                    'id' => 'etablissement-x',
                    'title' => 'Utilisez . comme séparateur décimal',
                    'class' => 'sbm-width-20c'
                ],
                'options' => [
                    'label' => 'X',
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
                'name' => 'y',
                'type' => 'text',
                'attributes' => [
                    'id' => 'etablissement-y',
                    'title' => 'Utilisez . comme séparateur décimal',
                    'class' => 'sbm-width-20c'
                ],
                'options' => [
                    'label' => 'Y',
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
                    'id' => 'etablissement-desservie',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Desservi',
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
                    'id' => 'etablissement-visible',
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
                    'id' => 'etablissement-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'etablissement-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
        $this->getInputFilter()
            ->get('rattacheA')
            ->setRequired(false);
    }

    public function modifFormForEdit()
    {
        $this->remove('etablissementId');
        $this->get('nom')->setAttribute('autofocus', 'autofocus');
        $this->add([
            'name' => 'etablissementId',
            'type' => 'hidden'
        ]);
        $this->add(
            [
                'name' => 'codeEtablissement',
                'type' => 'text',
                'attributes' => [
                    'id' => 'etablissement-codeid',
                    'disabled' => 'disabled',
                    'class' => 'sbm-text8'
                ],
                'options' => [
                    'label' => 'Code',
                    'label_attributes' => [
                        'class' => 'sbm-label170'
                    ],
                    'error_attributes' => [
                        'class' => 'form etablissement error error-codeid'
                    ]
                ]
            ]);
        return $this;
    }

    public function getInputFilterSpecification()
    {
        return [
            'etablissementId' => [
                'name' => 'etablissementId',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'Alnum'
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'SbmCommun\Model\Validator\CodeEtablissement'
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
            'aliasCG' => [
                'name' => 'aliasCG',
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
            'adresse1' => [
                'name' => 'adresse1',
                'required' => false
            ],
            'adresse2' => [
                'name' => 'adresse2',
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
            'niveau' => [
                'name' => 'niveau',
                'required' => true
            ],
            'statut' => [
                'name' => 'statut',
                'required' => true
            ],
            'visible' => [
                'name' => 'visible',
                'required' => false
            ],
            'desservie' => [
                'name' => 'desservie',
                'required' => false
            ],
            'regrPeda' => [
                'name' => 'regrPeda',
                'required' => false
            ],
            'rattacheA' => [
                'name' => 'rattacheA',
                'required' => false
            ],
            'telephone' => [
                'name' => 'telephone',
                'required' => false
            ],
            'fax' => [
                'name' => 'fax',
                'required' => false
            ],
            'email' => [
                'name' => 'email',
                'required' => false
            ],
            'directeur' => [
                'name' => 'directeur',
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
            'jOuverture' => [
                'name' => 'jOuverture',
                'required' => true
            ],
            'hMatin' => [
                'name' => 'hMatin',
                'required' => false
            ],
            'hMidi' => [
                'name' => 'hMidi',
                'required' => false
            ],
            'hAMidi' => [
                'name' => 'hAMidi',
                'required' => false
            ],
            'hSoir' => [
                'name' => 'hSoir',
                'required' => false
            ],
            'hGarderieOMatin' => [
                'name' => 'hGarderieOMatin',
                'required' => false
            ],
            'hGarderieFMidi' => [
                'name' => 'hGarderieFMidi',
                'required' => false
            ],
            'hGarderieFSoir' => [
                'name' => 'hGarderieFSoir',
                'required' => false
            ],
            'x' => [
                'name' => 'x',
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
            'y' => [
                'name' => 'y',
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
            ]
        ];
    }

    public function setData($data)
    {
        parent::setData($data);
        if ($this->has('codeEtablissement')) {
            $e = $this->get('codeEtablissement');
            $e->setValue($this->get('etablissementId')
                ->getValue());
        }
    }
}