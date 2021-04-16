<?php
/**
 * Formulaire de saisie pour un correspondant
 *
 * Un correspondant est un invité rattaché à un élève par eleveId et qui prend les mêmes services de
 * transport que cet élève.
 * Les champs millesime, eleveId, gratuit sont cachés et initialisé au chargement du formulaire.
 * Les champs nom, prenom, sexe, nationalite, identifiantSejour, dateDebut, dateFin,
 * demande, motifRefus et commentaire sont modifiables.
 * Les champs etablissementId, chez, adresseL1, adresseL2, adresseL3, codePostal, communeId,
 * joursTransport, stationId, duplicata, paiement, inscrit ne sont pas utilisés
 *
 * @project sbm
 * @package SbmCommun/src/Form
 * @filesource Correspondant.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 oct. 2020
 * @version 2020-2.6.1
 */
namespace SbmCommun\Form;

use SbmBase\Model\Session;
use Zend\InputFilter\InputFilterProviderInterface;
use Traversable;
use Zend\Stdlib\ArrayUtils;

class Correspondant extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     *
     * @var int
     */
    private $eleveId;

    /**
     *
     * @var int
     */
    private $millesime;

    public function __construct(string $name = 'correspondant', $options = [])
    {
        $this->millesime = Session::get('millesime');
        $this->eleveId = 0;
        parent::__construct($name, $options);
        $this->add([
            'name' => 'millesime',
            'type' => 'hidden'
        ])
            ->add([
            'name' => 'eleveId',
            'type' => 'hidden'
        ])
            ->add(
            [
                'name' => 'gratuit',
                'type' => 'hidden',
                'attributes' => [
                    'value' => 1
                ]
            ])
            ->add([
            'name' => 'inviteId',
            'type' => 'hidden'
        ])
            ->add(
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
                'name' => 'nom',
                'type' => 'SbmCommun\Form\Element\NomPropre',
                'attributes' => [
                    'id' => 'nom',
                    'class' => 'nom',
                    'autofocus' => 'autofocus'
                ],
                'options' => [
                    'label' => 'Nom',
                    'label_attributes' => [
                        'class' => 'sbm-label nom'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'prenom',
                'type' => 'SbmCommun\Form\Element\Prenom',
                'attributes' => [
                    'id' => 'prenom',
                    'class' => 'prenom'
                ],
                'options' => [
                    'label' => 'Prénom',
                    'label_attributes' => [
                        'class' => 'sbm-label prenom'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'sexe',
                'type' => 'Zend\Form\Element\Radio',
                'attributes' => [
                    'id' => 'sexe',
                    'class' => 'sexe'
                ],
                'options' => [
                    'label' => 'Sexe',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ],
                    'value_options' => [
                        1 => 'Masculin',
                        2 => 'Féminin'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'nationalite',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'nationalite',
                    'class' => 'nationalite'
                ],
                'options' => [
                    'label' => 'Nationalité',
                    'label_attributes' => [
                        'class' => 'sbm-label nationalite'
                    ],
                    'empty_option' => 'Choisir dans la liste',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'identifiantSejour',
                'type' => 'text',
                'attributes' => [
                    'id' => 'identifantSejour',
                    'class' => 'identifiantSejour'
                ],
                'options' => [
                    'label' => 'Désignation du séjour',
                    'label_attributes' => [
                        'class' => 'sbm-label identifiantSejour'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'dateDebut',
                'type' => 'Zend\Form\Element\Date',
                'attributes' => [
                    'id' => 'dateDebut',
                    'class' => 'date'
                ],
                'options' => [
                    'label' => 'Date de début du séjour',
                    'label_attributes' => [
                        'class' => 'sbm-label date'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'dateFin',
                'type' => 'Zend\Form\Element\Date',
                'attributes' => [
                    'id' => 'dateFin',
                    'class' => 'date'
                ],
                'options' => [
                    'label' => 'Date de fin du séjour',
                    'label_attributes' => [
                        'class' => 'sbm-label date'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'demande',
                'type' => 'Zend\Form\Element\Radio',
                'attributes' => [
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
                            'attributes' => [
                                'id' => 'demanderadio0'
                            ],
                            'label' => 'Refusée',
                        ],
                        [
                            'value' => '1',
                            'attributes' => [
                                'id' => 'demanderadio1'
                            ],
                            'label' => 'A traiter'
                        ],
                        [
                            'value' => '2',
                            'attributes' => [
                                'id' => 'demanderadio2'
                            ],
                            'label' => 'Acceptée'
                        ]
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'motifRefus',
                'type' => 'Zend\Form\Element\Textarea',
                'attributes' => [
                    'id' => 'motifRefus',
                    'class' => 'note motifRefus'
                ],
                'options' => [
                    'label' => 'Motif du refus',
                    'label_attributes' => [
                        'class' => 'sbm-label note motifRefus'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'commentaire',
                'type' => 'Zend\Form\Element\Textarea',
                'attributes' => [
                    'id' => 'motifRefus',
                    'class' => 'note commentaire'
                ],
                'options' => [
                    'label' => 'Commentaire',
                    'label_attributes' => [
                        'class' => 'sbm-label note commentaire'
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
                    'id' => 'correspondant-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'button default submit'
                ]
            ])->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'correspondant-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    /**
     *
     * @param int $eleveId
     * @param int $millesime
     * @return \SbmCommun\Form\Correspondant
     */
    public function initialise(int $eleveId, int $millesime = 0)
    {
        $this->eleveId = $eleveId;
        if ($millesime) {
            $this->millesime = $millesime;
        }
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Zend\Form\Form::setData()
     */
    public function setData($data)
    {
        if ($this->eleveId <= 0) {
            throw new \SbmBase\Model\Exception\RuntimeException(
                "L'élève accueillant n'est pas identifié.");
        }
        if ($data instanceof Traversable) {
            $data = ArrayUtils::iteratorToArray($data);
        }
        $data = array_merge($data,
            [
                'millesime' => $this->millesime,
                'eleveId' => $this->eleveId
            ]);
        return parent::setData($data);
    }

    public function getInputFilterSpecification()
    {
        return [
            'inviteId' => [
                'name' => 'inviteId',
                'required' => false
            ],
            'millesime' => [
                'name' => 'millesime',
                'required' => true,
                'validators' => [
                    [
                        'name' => 'Between',
                        'options' => [
                            'min' => $this->millesime,
                            'max' => $this->millesime,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'eleveId' => [
                'name' => 'eleveId',
                'required' => true,
                'validators' => [
                    [
                        'name' => 'GreaterThan',
                        'options' => [
                            'min' => 1,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'sexe' => [
                'name' => 'sexe',
                'required' => true
            ],
            'nationalite' => [
                'name' => 'nationalite',
                'required' => false
            ],
            'identifiantSejour' => [
                'name' => 'identifiantSejour',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'dateDebut' => [
                'name' => 'dateDebut',
                'required' => true
            ],
            'dateFin' => [
                'name' => 'dateFin',
                'required' => true
            ],
            'demande' => [
                'name' => 'demande',
                'required' => true
            ],
            'motifRefus' => [
                'name' => 'motifRefus',
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
}