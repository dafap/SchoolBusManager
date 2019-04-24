<?php
/**
 * Formulaire de saisie et modification d'un lot de marché
 *
 * @project sbm
 * @package SbmCommun/src/Form
 * @filesource Lot.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Lot extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('lot');
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
            'name' => 'lotId',
            'type' => 'hidden'
        ]);
        $this->add(
            [
                'name' => 'marche',
                'type' => 'text',
                'attributes' => [
                    'id' => 'lot-marche',
                    'autofocus' => 'autofocus',
                    'class' => 'marche'
                ],
                'options' => [
                    'label' => 'Désignation du marché',
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
                'name' => 'lot',
                'type' => 'text',
                'attributes' => [
                    'id' => 'lot-lot',
                    'class' => 'lot'
                ],
                'options' => [
                    'label' => 'Désignation du service',
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
                'name' => 'libelle',
                'type' => 'text',
                'attributes' => [
                    'id' => 'lot-libelle',
                    'class' => 'libelle'
                ],
                'options' => [
                    'label' => 'Désignation du lot',
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
                'name' => 'complement',
                'type' => 'text',
                'attributes' => [
                    'id' => 'lot-complement',
                    'class' => 'complement'
                ],
                'options' => [
                    'label' => 'Complément',
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
                'name' => 'transporteurId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'lot-transporteurId',
                    'class' => 'transporteurId'
                ],
                'options' => [
                    'label' => 'Titulaire',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un transporteur',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'dateDebut',
                'type' => 'Zend\Form\Element\Date',
                'attributes' => [
                    'id' => 'lot-dateDebut',
                    'class' => 'dateDebut'
                ],
                'options' => [
                    'label' => 'Date début convention',
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
                'name' => 'dateFin',
                'type' => 'Zend\Form\Element\Date',
                'attributes' => [
                    'id' => 'lot-dateFin',
                    'class' => 'dateFin'
                ],
                'options' => [
                    'label' => 'Date fin convention',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'actif',
                'attributes' => [
                    'id' => 'lot-actif',
                    'class' => 'actif'
                ],
                'options' => [
                    'label' => 'Marché en cours ?',
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
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'commentaire',
                'attributes' => [
                    'id' => 'lot-commentaire',
                    'class' => 'commentaire'
                ],
                'options' => [
                    'label' => 'Notes',
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
                    'id' => 'lot-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'lot-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'marche' => [
                'name' => 'marche',
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
            'lot' => [
                'name' => 'lot',
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
            'libelle' => [
                'name' => 'libelle',
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

            'complement' => [
                'name' => 'complement',
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
            'transporteurId' => [
                'name' => 'transporteurId',
                'required' => true
            ],
            'dateDebut' => [
                'name' => 'dateDebut',
                'required' => true
            ],
            'dateFin' => [
                'name' => 'dateFin',
                'required' => true
            ],
            'actif' => [
                'name' => 'actif',
                'required' => false
            ],
            'commentaire' => [
                'name' => 'commentaire',
                'required' => false
            ]
        ];
    }
}
