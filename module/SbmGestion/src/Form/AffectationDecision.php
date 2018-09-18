<?php
/**
 * Formulaire d'affectation et de décision d'une demande de transport
 *
 * @project sbm
 * @package SbmGestion/Form
 * @filesource AffectationDecision.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmGestion\Form;

use SbmCommun\Form\AbstractSbmForm as Form;
use Zend\InputFilter\InputFilterProviderInterface;

class AffectationDecision extends Form implements InputFilterProviderInterface
{

    /**
     * Correspond au n° de trajet.
     * Prend la valeur 1 ou 2 selon qu'il s'agit du trajet 1 ou 2
     *
     * @var int
     */
    private $trajet;

    /**
     * Correspond au n° de la phase d'inscription
     *
     * @var int
     */
    private $phase;

    /**
     * Constructeur du formulaire
     *
     * @param int $trajet
     *            Correspond au n° de trajet. Prend la valeur 1 ou 2 selon qu'il s'agit du trajet 1
     *            ou 2
     * @param int $phase
     *            Correspond au n° de phase. Prend la valeur 1 ou 2 selon qu'il s'agit de la phase
     *            1 ou 2 de l'affectation
     */
    public function __construct($trajet, $phase)
    {
        $this->trajet = $trajet;
        $this->phase = $phase;
        parent::__construct('decision');
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
        // les hiddens reçus en post et à transmettre à nouveau
        foreach ([
            'eleveId',
            'millesime',
            'trajet',
            'jours',
            'sens',
            'correspondance',
            'responsableId',
            'demandeR' . $trajet,
            'op'
        ] as $name) {
            $this->add([
                'name' => $name,
                'type' => 'hidden'
            ]);
        }

        if ($phase == 1) {
            $this->preparePhase1();
        } else {
            $this->preparePhase2();
            $this->add(
                [
                    'name' => 'back',
                    'attributes' => [
                        'type' => 'submit',
                        'value' => 'Précédent',
                        'id' => 'decision-cancel',
                        'autofocus' => 'autofocus',
                        'class' => 'button default cancel'
                    ]
                ]);
        }
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'decision-cancel',
                    'autofocus' => 'autofocus',
                    'class' => 'button default cancel'
                ]
            ]);
        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Valider',
                    'id' => 'decision-submit',
                    'class' => 'button default submit'
                ]
            ]);
    }

    /**
     * Crée les éléments du formulaire pour la phase 1
     */
    private function preparePhase1()
    {
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'fictif',
                'attributes' => [
                    'id' => 'decision_district',
                    'class' => 'sbm-checkbox',
                    'disabled' => 'disabled'
                ],
                'options' => [
                    'label' => 'Secteur scolaire',
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
                'name' => 'derogation',
                'attributes' => [
                    'id' => 'decision_derogation',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Dérogation',
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
                'type' => 'text',
                'name' => 'motifDerogation' . $this->trajet,
                'attributes' => [
                    'id' => 'decision_motifDerogation',
                    'class' => 'sbm-width-35c'
                ],
                'options' => [
                    'label' => 'Motif de la dérogation',
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
                'name' => 'accordR' . $this->trajet,
                'attributes' => [
                    'id' => 'decision_accordR',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Transport accepté',
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
                'type' => 'text',
                'name' => 'motifRefusR' . $this->trajet,
                'attributes' => [
                    'id' => 'decision_motifRefusR',
                    'class' => 'sbm-width-35c'
                ],
                'options' => [
                    'label' => 'Motif du refus',
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
                'name' => 'subventionR' . $this->trajet,
                'attributes' => [
                    'id' => 'decision_subventionR',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Subvention attribuée',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    /**
     * Crée les éléments du formulaire pour la phase 2
     */
    private function preparePhase2()
    {
        $this->add(
            [
                'name' => 'service1Id',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'affectation-service1Id',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Circuit',
                    'label_attributes' => [
                        'class' => 'sbm-form-auto'
                    ],
                    'empty_option' => 'Choisissez un circuit',
                    // 'allow_empty' => true,
                    // 'disable_inarray_validator' => false,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'station1Id',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'affectation-station1Id',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Point de montée',
                    'label_attributes' => [
                        'class' => 'sbm-form-auto'
                    ],
                    'empty_option' => 'Choisissez une station',
                    // 'allow_empty' => true,
                    // 'disable_inarray_validator' => false,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'station2Id',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'affectation-station2Id',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Point de descente',
                    'label_attributes' => [
                        'class' => 'sbm-form-auto'
                    ],
                    'empty_option' => 'Choisissez une station',
                    'allow_empty' => true,
                    'disable_inarray_validator' => false,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'service2Id',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'affectation-service2Id',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Correspondance',
                    'label_attributes' => [
                        'class' => 'sbm-form-auto'
                    ],
                    'empty_option' => 'Choisissez un circuit',
                    'allow_empty' => true,
                    'disable_inarray_validator' => false,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    /**
     * En phase 1, pour afficher un checkbox disabled avec la valeur de district, car l'élément
     * district ne fait pas partie du formulaire
     * Dans tous les cas, la sortie du formulaire doit se faire avec un demandeR1 ou un demandeR2
     * (selon trajet) égal à 2 (demandé et traité)
     *
     * (non-PHPdoc)
     *
     * @see \Zend\Form\Form::setData()
     */
    public function setData($data)
    {
        if ($this->phase == 1 && array_key_exists('district', $data)) {
            $fictif = $this->get('fictif');
            $fictif->setValue($data['district']);
        }
        parent::setData($data);
        $demande = $this->get('demandeR' . $this->trajet);
        $demande->setValue(2);
        return $this;
    }

    public function getInputFilterSpecification()
    {
        if ($this->phase == 1) {
            return [
                'fictif' => [
                    'name' => 'fictif',
                    'required' => false
                ],
                'derogation' => [
                    'name' => 'derogation',
                    'required' => true
                ],
                'accordR' . $this->trajet => [
                    'name' => 'accordR' . $this->trajet,
                    'required' => true
                ],
                'subventionR' . $this->trajet => [
                    'name' => 'subventionR' . $this->trajet,
                    'required' => true
                ],
                'motifDerogation' . $this->trajet => [
                    'name' => 'motifDerogation' . $this->trajet,
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
                'motifRefusR' . $this->trajet => [
                    'name' => 'motifRefusR' . $this->trajet,
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
        } else {
            return [
                'station2Id' => [
                    'name' => 'station2Id',
                    'required' => false
                ],
                'service2Id' => [
                    'name' => 'service2Id',
                    'required' => false
                ]
            ];
        }
    }
}