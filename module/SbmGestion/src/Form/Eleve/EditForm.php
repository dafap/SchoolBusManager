<?php
/**
 * Formulaire de modification d'un élève
 *
 * @project sbm
 * @package SbmGestion/Form/Eleve
 * @filesource EditForm.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Form\Eleve;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class EditForm extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct($options = [])
    {
        parent::__construct('eleve', $options);
        $this->setAttribute('method', 'post');
        $this->add([
            'name' => 'eleveId',
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
                'type' => 'SbmCommun\Form\Element\NomPropre',
                'name' => 'nom',
                'attributes' => [
                    'id' => 'eleve-nom',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Nom de l\'élève',
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
                'type' => 'SbmCommun\Form\Element\Prenom',
                'name' => 'prenom',
                'attributes' => [
                    'id' => 'eleve-prenom',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Prénom de l\'élève',
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
                'type' => 'Zend\Form\Element\Select',
                'name' => 'sexe',
                'attributes' => [
                    'id' => 'eleve-sexe'
                ],
                'options' => [
                    'label' => 'Sexe',
                    'label_options' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Quel sexe ?',
                    'value_options' => [
                        1 => 'masculin',
                        2 => 'féminin'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Date',
                'name' => 'dateN',
                'attributes' => [
                    'id' => 'eleve-dateN',
                    'class' => 'sbm-text15'
                ],
                'options' => [
                    'label' => 'Date de naissance',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ],
                    'format' => 'Y-m-d'
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'etablissementId',
                'attributes' => [
                    'id' => 'eleve-etablissementId',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Etablissement',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un établissement scolaire',
                    'error_attributes' => [
                        'class' => 'sbm_error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'classeId',
                'attributes' => [
                    'id' => 'eleve-classeId',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Classe',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez une classe',
                    'error_attributes' => [
                        'class' => 'sbm_error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'regimeId',
                'options' => [
                    'label' => 'Régime',
                    'label_attributes' => [
                        'class' => 'sbm-radio regime'
                    ],
                    'value_options' => [
                        [
                            'value' => '0',
                            'attributes' => [
                                'id' => 'regimeidradio0dp'
                            ],
                            'label' => 'DP',
                            'label_attributes' => [
                                'class' => 'label-radio dp'
                            ]
                        ],
                        [
                            'value' => '1',
                            'attributes' => [
                                'id' => 'regimeidradio1in'
                            ],
                            'label' => 'interne',
                            'label_attributes' => [
                                'class' => 'label-radio interne'
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'district',
                'attributes' => [
                    'id' => 'eleve-district',
                    'disabled' => 'disabled'
                ],
                'options' => [
                    'label' => 'District',
                    'label_attributes' => [
                        'class' => 'sbm-label checkbox'
                    ],
                    'use_hidden_element' => true,
                    'checked_value' => '1',
                    'unchecked_value' => '0'
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'derogation',
                'attributes' => [
                    'id' => 'eleve-derogation'
                ],
                'options' => [
                    'label' => 'Dérogation',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'value_options' => [
                        '0' => 'Non',
                        '1' => 'Dérogation',
                        '2' => 'Non ayant-droit'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm_error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'motifDerogation',
                'attributes' => [
                    'id' => 'eleve-motifDerogation',
                ],
                'options' => [
                    /*
                     * 'label' => 'Motif', 'label_attributes' => [ 'class' => 'sbm-label'
                     * ],
                     */
                    'error_attributes' => [
                        'class' => 'sbm_error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'name' => 'joursTransport',
                'attributes' => [
                    // pas de id car l'id n'est placé que sur la première option du
                    // multicheckbox
                    'class' => 'sbm-multicheckbox'
                ],
                'options' => [
                    'label' => 'Demande de transport',
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
                'name' => 'fa',
                'attributes' => [
                    'id' => 'eleve-fa'
                ],
                'options' => [
                    'label' => 'Famille d\'accueil',
                    'label_attributes' => [
                        'class' => 'sbm-label checkbox'
                    ],
                    'use_hidden_element' => true,
                    'checked_value' => '1',
                    'unchecked_value' => '0'
                ]
            ]);
        /*
         * $this->add([ 'type' => 'Zend\Form\Element\Checkbox', 'name' => 'gratuit',
         * 'attributes' => [ 'id' => 'eleve-gratuit' ], 'options' => [ 'label' =>
         * 'Gratuité', 'label_attributes' => [ 'class' => 'sbm-label checkbox' ],
         * 'use_hidden_element' => true, 'checked_value' => '1', 'unchecked_value' => '0'
         * ] ]);
         */
        $this->add(
            [
                'type' => 'Zend\Form\Element\Button',
                'name' => 'btnpaiement',
                'attributes' => [
                    'id' => 'eleve-btnpaiement'
                ],
                'options' => [
                    'label' => 'Paiement',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'ga',
                'attributes' => [
                    'id' => 'eleve-ga'
                ],
                'options' => [
                    'label' => 'Garde alternée',
                    'label_attributes' => [
                        'class' => 'sbm-label checkbox'
                    ],
                    'use_hidden_element' => true,
                    'checked_value' => '1',
                    'unchecked_value' => '0'
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'anneeComplete',
                'attributes' => [
                    'id' => 'eleve-anneeComplete'
                ],
                'options' => [
                    'label' => 'Année complète',
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
                'type' => 'Zend\Form\Element\Date',
                'name' => 'dateDebut',
                'attributes' => [
                    'id' => 'eleve-dateDebut',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Début',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ],
                    'format' => 'Y-m-d'
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Date',
                'name' => 'dateFin',
                'attributes' => [
                    'id' => 'eleve-dateFin',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Fin',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ],
                    'format' => 'Y-m-d'
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'responsable1Id',
                'attributes' => [
                    'id' => 'eleve-responsable1Id',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Responsable',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un responsable',
                    'error_attributes' => [
                        'class' => 'sbm_error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'responsable2Id',
                'attributes' => [
                    'id' => 'eleve-responsable2Id',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Responsable',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un responsable',
                    'error_attributes' => [
                        'class' => 'sbm_error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'demandeR1',
                'attributes' => [
                    'id' => 'eleve-demandeR1',
                    'class' => 'sbm-radio'
                ],
                'options' => [
                    'label' => 'Demande',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ],
                    'value_options' => [
                        [
                            'value' => '0',
                            'label' => 'Non',
                            'attributes' => [
                                'id' => 'demander1radio0'
                            ]
                        ],
                        [
                            'value' => '1',
                            'label' => 'A traiter',
                            'attributes' => [
                                'id' => 'demander1radio1'
                            ]
                        ],
                        [
                            'value' => '2',
                            'label' => 'Traitée',
                            'attributes' => [
                                'id' => 'demander1radio2'
                            ]
                        ]
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'demandeR2',
                'attributes' => [
                    'id' => 'eleve-demandeR2',
                    'class' => 'sbm-radio'
                ],
                'options' => [
                    'label' => 'Demande',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ],
                    'value_options' => [
                        [
                            'value' => '0',
                            'label' => 'Non',
                            'attributes' => [
                                'id' => 'demander2radio0'
                            ]
                        ],
                        [
                            'value' => '1',
                            'label' => 'A traiter',
                            'attributes' => [
                                'id' => 'demander2radio1'
                            ]
                        ],
                        [
                            'value' => '2',
                            'label' => 'Traitée',
                            'attributes' => [
                                'id' => 'demander2radio2'
                            ]
                        ]
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'distanceR1',
                'type' => 'SbmCommun\Form\Element\IsDecimal',
                'attributes' => [
                    'id' => 'eleve-distanceR1',
                    'class' => 'sbm-width-10c',
                    'title' => 'Double-clic pour calculer la distance',
                    'autocomplete' => 'off'
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
                'name' => 'distanceR2',
                'type' => 'SbmCommun\Form\Element\IsDecimal',
                'attributes' => [
                    'id' => 'eleve-distanceR2',
                    'class' => 'sbm-width-10c',
                    'title' => 'Double-clic pour calculer la distance',
                    'autocomplete' => 'off'
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'accordR1',
                'attributes' => [
                    'id' => 'eleve-accordR1'
                ],
                'options' => [
                    'label' => 'Accord',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'accordR2',
                'attributes' => [
                    'id' => 'eleve-accordR2'
                ],
                'options' => [
                    'label' => 'Accord',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'subventionR1',
                'attributes' => [
                    'id' => 'eleve-subventionR1'
                ],
                'options' => [
                    'label' => 'Subvention',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'subventionR2',
                'attributes' => [
                    'id' => 'eleve-subventionR2'
                ],
                'options' => [
                    'label' => 'Subvention',
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
                'name' => 'motifRefusR1',
                'attributes' => [
                    'id' => 'eleve-motifRefusR1',
                    'class' => 'demande-motifRefus'
                ],
                'options' => [
                    /*
                     * 'label' => 'Motif', 'label_attributes' => [ 'class' => 'sbm-label'
                     * ],
                     */
                    'error_attributes' => [
                        'class' => 'sbm_error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'motifRefusR2',
                'attributes' => [
                    'id' => 'eleve-motifRefusR2',
                    'class' => 'demande-motifRefus'
                ],
                'options' => [
                    /*
                     * 'label' => 'Motif', 'label_attributes' => [ 'class' => 'sbm-label'
                     * ],
                     */
                    'error_attributes' => [
                        'class' => 'sbm_error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'commentaire',
                'attributes' => [
                    'id' => 'eleve-commentaire',
                    'class' => 'commentaire'
                ],
                'options' => [
                    'label' => 'Notes annuelles',
                    'label_attributes' => [
                        'class' => 'sbm-label commentaire'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm_error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'note',
                'attributes' => [
                    'id' => 'eleve-note',
                    'class' => 'commentaire'
                ],
                'options' => [
                    'label' => 'Notes permanentes',
                    'label_attributes' => [
                        'class' => 'sbm-label commentaire'
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

    /**
     * Description des contraintes, filtres et validateurs
     * (non-PHPdoc)
     *
     * @see \Zend\InputFilter\InputFilterProviderInterface::getInputFilterSpecification()
     */
    public function getInputFilterSpecification()
    {
        return [
            'district' => [
                'name' => 'district',
                'required' => false
            ],
            'responsable2Id' => [
                'name' => 'responsable2Id',
                'required' => false
            ],
            'demandeR2' => [
                'name' => 'demandeR2',
                'required' => false
            ]
        ];
    }

    public function setData($data)
    {
        parent::setData($data);
        $ga = $this->get('ga');
        $ga->setValue(! empty($data['responsable2Id']));
    }
}