<?php
/**
 * Formulaire des critères pour filtrer la liste des élèves
 *
 * @project sbm
 * @package SbmGestion/Form/Eleve
 * @filesource CriteresForm.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 août 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Form\Eleve;

use SbmCommun\Form\CriteresForm as SbmCommunCriteresForm;
use Zend\InputFilter\InputFilterProviderInterface;

class CriteresForm extends SbmCommunCriteresForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('criteres');
        $this->setAttribute('method', 'post');

        $this->add(
            [
                'type' => 'text',
                'name' => 'numero',
                'attributes' => [
                    'id' => 'critere-numero',
                    'maxlength' => '11',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Numéro',
                    'label_attributes' => [
                        'class' => 'sbm-first'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'nomSA',
                'attributes' => [
                    'id' => 'critere-nomSA',
                    'maxlength' => '45',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Nom',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'prenomSA',
                'attributes' => [
                    'id' => 'critere-prenomSA',
                    'maxlength' => '45',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Prénom',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'responsable',
                'attributes' => [
                    'id' => 'critere-responsable',
                    'maxlength' => '45',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Responsable',
                    'label_attributes' => [
                        'class' => 'sbm-new-line'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'etablissementId',
                'attributes' => [
                    'id' => 'critere-etablissementId',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Etablissement',
                    'empty_option' => 'Tout',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'classeId',
                'attributes' => [
                    'id' => 'critere-classeId',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Classe',
                    'empty_option' => 'Tout',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'etat',
                'attributes' => [
                    'id' => 'critere-etat',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Etat',
                    'label_attributes' => [
                        'class' => 'sbm-new-line'
                    ],
                    'empty_option' => 'Tout',
                    'value_options' => [
                        'inscription' => [
                            'label' => 'état de l\'inscription',
                            'options' => [
                                '1' => 'Payés',
                                '2' => 'Impayés'
                            ]
                        ],
                        'fiche' => [
                            'label' => 'état de la fiche',
                            'options' => [
                                '3' => 'Rayés',
                                '4' => 'Non rayés'
                            ]
                        ],
                        'photo' => [
                            'label' => 'état des photos',
                            'options' => [
                                '5' => 'Avec photo',
                                '6' => 'Sans photo'
                            ]
                        ]
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'demande',
                'attributes' => [
                    'id' => 'critere-demande',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Demandes',
                    'empty_option' => 'Tout',
                    'value_options' => [
                        '1' => 'Non traitées',
                        '2' => 'Partiellement traitées',
                        '3' => 'Traitées'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'decision',
                'attributes' => [
                    'id' => 'critere-decision',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Décisions',
                    'empty_option' => 'Tout',
                    'value_options' => [
                        '1' => 'Accord total',
                        '2' => 'Accord partiel',
                        '3' => 'Subvention',
                        '4' => 'Refus'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'incomplet',
                'attributes' => [
                    'id' => 'critere-incomplet',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Fiches incomplètes',
                    'empty_option' => 'Toutes',
                    'value_options' => [
                        '1' => 'Distance à calculer',
                        '2' => 'Sans affectation',
                        '3' => 'Sans photo'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'particularite',
                'attributes' => [
                    'id' => 'critere-particularite',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Particularité',
                    'empty_option' => 'Tout',
                    'value_options' => [
                        '1' => 'Garde alternée',
                        //'2' => 'Famille d\'accueil',
                        '3' => 'Dérogation accordée',
                        '4' => 'Non ayants droit acceptés'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'selection',
                'attributes' => [
                    'type' => 'checkbox',
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sélectionnés',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'submit',
                'name' => 'submit',
                'attributes' => [
                    'title' => 'Rechercher',
                    'id' => 'criteres-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'fam-find button submit'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'numero' => [
                'name' => 'numero',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'nomSA' => [
                'name' => 'nomSA',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'prenomSA' => [
                'name' => 'prenomSA',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'responsable' => [
                'name' => 'responsable',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'etablissementId' => [
                'name' => 'etablissementId',
                'required' => false
            ],
            'classeId' => [
                'name' => 'classeId',
                'required' => false
            ],
            'etat' => [
                'name' => 'etat',
                'required' => false
            ],
            'demande' => [
                'name' => 'demande',
                'required' => false
            ],
            'decision' => [
                'name' => 'decision',
                'required' => false
            ],
            'incomplet' => [
                'name' => 'incomplet',
                'required' => false
            ],
            'particularite' => [
                'name' => 'particularite',
                'required' => false
            ],
            'selection' => [
                'name' => 'selection',
                'required' => false
            ]
        ];
    }
}