<?php
/**
 * Formulaire de création d'un élève (phase 2)
 *
 * La création d'un élève se fait en plusieurs phases :
 * (Form1) : demande du nom, prénom, date de naissance, responsable1, garde_alternée, responsable2 (facultatif)
 * - recherche dans la base s'il existe des élèves ayant ces caractéristiques
 * - si oui, affichage de la liste trouvée (Liste1) avec possibilité de choisir un élève (21) ou de créer un nouvel élève (22)
 * - si non, création d'un nouvel élève (22)
 * (21) : recherche dans la table scolarites en année courante si la fiche existe
 * - si oui, passage en mode modification FIN
 * - si non, création de la scolarite (31)
 * (22) : enregistre le formulaire (Form1) et récupère le eleveId puis création de la scolarité (31)
 * (31) : formulaire (Form2) pour saisir la scolarité (sans les éléments de décision) : etablissement, classe, joursTransport, fa, demandeR1, demandeR2, commentaire
 * - enregistre la scolarité
 * - passe en mode modification FIN
 *
 * @project sbm
 * @package SbmGestion/Form/Eleve
 * @filesource AddElevePhase2.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Form\Eleve;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class AddElevePhase2 extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct($options = [])
    {
        parent::__construct('eleve', $options);
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
            'name' => 'eleveId',
            'type' => 'hidden'
        ]);
        $this->add(
            [
                'name' => 'responsable1Id',
                'type' => 'hidden',
                'attributes' => [
                    'id' => 'eleve-responsable1Id'
                ]
            ]);
        $this->add(
            [
                'name' => 'responsable2Id',
                'type' => 'hidden',
                'attributes' => [
                    'id' => 'eleve-responsable2Id'
                ]
            ]);
        // saisies de la phase 2
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'anneeComplete',
                'attributes' => [
                    'id' => 'eleve-anneeComplete',
                    'value' => '1'
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
                    'empty_option' => 'Choisissez l\'établissement scolaire',
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
                    'empty_option' => 'Choisissez la classe',
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
                    'value' => '1'
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
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'paiement',
                'attributes' => [
                    'id' => 'eleve-paiement',
                    'value' => '0'
                ],
                'options' => [
                    'label' => 'Paiement',
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
                'name' => 'derogation',
                'attributes' => [
                    'id' => 'eleve-derogation'
                ],
                'options' => [
                    'label' => 'Dérogation',
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
                'name' => 'motifDerogation',
                'attributes' => [
                    'id' => 'eleve-motifDerogation',
                    'class' => 'sbm-width-40c'
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
                    'id' => 'eleve_joursTransport',
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
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'demandeR1',
                'attributes' => [
                    'id' => 'eleve-demandeR1',
                    'class' => 'sbm-radio ouinon'
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
                            'label' => 'Oui',
                            'attributes' => [
                                'id' => 'demander1radio1',
                                'checked' => 'checked'
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
                    'class' => 'sbm-radio ouinon'
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
                                'id' => 'demander2radio0',
                                'checked' => 'checked'
                            ]
                        ],
                        [
                            'value' => '1',
                            'label' => 'Oui',
                            'attributes' => [
                                'id' => 'demander2radio1'
                            ]
                        ]
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'commentaire',
                'attributes' => [
                    'id' => 'eleve-commentaire'
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

        $inputFilter = $this->getInputFilter();
        $distanceR2 = $inputFilter->get('distanceR2');
        $distanceR2->setRequired(false);
    }

    /**
     * Description des contraintes, filtres et validateurs (non-PHPdoc)
     *
     * @see \Zend\InputFilter\InputFilterProviderInterface::getInputFilterSpecification()
     */
    public function getInputFilterSpecification()
    {
        return [
            'demandeR2' => [
                'name' => 'demandeR2',
                'required' => false
            ]
        ];
    }
}