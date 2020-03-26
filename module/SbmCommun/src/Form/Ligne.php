<?php
/**
 * Formulaire de saisie et modification d'une ligne
 *
 * @project sbm
 * @package SbmCommun/src/Form
 * @filesource Ligne.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;
use SbmBase\Model\Session;

class Ligne extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     * Permet de faire savoir à getInputFilterSpecification() si on est en modif (true) ou
     * en ajout (false)
     *
     * @var bool
     */
    private $edit;

    public function __construct()
    {
        $this->edit = false;
        parent::__construct('ligne');
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
            'name' => 'id',
            'type' => 'hidden'
        ]);
        $this->add([
            'name' => 'millesime',
            'type' => 'hidden'
        ]);
        $this->add(
            [
                'name' => 'ligneId',
                'type' => 'text',
                'attributes' => [
                    'id' => 'ligne-ligneid',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Code de la ligne',
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
                'name' => 'operateur',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'ligne-operateur',
                    'class' => 'sbm-width-20c'
                ],
                'options' => [
                    'label' => 'Opérateur',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un opérateur',
                    'value_options' => [],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'lotId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'ligne-lotid',
                    'class' => 'sbm-width-25c'
                ],
                'options' => [
                    'label' => 'Lot du marché',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un lot de marché',
                    'allow_empty' => true,
                    'disable_inarray_validator' => false,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'extremite1',
                'type' => 'text',
                'attributes' => [
                    'id' => 'ligne-extremite1',
                    'class' => 'designation'
                ],
                'options' => [
                    'label' => 'Désignation du point de départ',
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
                'name' => 'extremite2',
                'type' => 'text',
                'attributes' => [
                    'id' => 'ligne-extremite2',
                    'class' => 'libelle'
                ],
                'options' => [
                    'label' => 'Désignation du terminus',
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
                'name' => 'via',
                'type' => 'text',
                'attributes' => [
                    'id' => 'ligne-via',
                    'class' => 'complement'
                ],
                'options' => [
                    'label' => 'Point de passage caractéristique',
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
                'name' => 'internes',
                'attributes' => [
                    'id' => 'ligne-internes',
                    'class' => 'internes'
                ],
                'options' => [
                    'label' => 'Ligne réservée aux internes ?',
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
                'name' => 'actif',
                'attributes' => [
                    'id' => 'ligne-actif',
                    'class' => 'actif'
                ],
                'options' => [
                    'label' => 'Ligne ouverte ?',
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
                    'id' => 'ligne-commentaire',
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
                    'id' => 'ligne-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'ligne-cancel',
                    'class' => 'button default cancel'
                ]
            ]);

        $this->getInputFilter()
            ->get('lotId')
            ->setRequired(false);
    }

    public function getInputFilterSpecification()
    {
        $spec = [
            'ligneId' => [
                'name' => 'ligneId',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    // met en majuscules, y compris les lettres accentuées et ligatures
                    [
                        'name' => 'Zend\Filter\StringToUpper',
                        'options' => [
                            'encoding' => 'utf-8'
                        ]
                    ]
                ]
            ],
            'lotId' => [
                'name' => 'lotId',
                'required' => false
            ],
            'extremite1' => [
                'name' => 'extremite1',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    // met en majuscules, y compris les lettres accentuées et ligatures
                    [
                        'name' => 'Zend\Filter\StringToUpper',
                        'options' => [
                            'encoding' => 'utf-8'
                        ]
                    ]
                ]
            ],
            'extremite2' => [
                'name' => 'extremite2',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'Zend\Filter\StringToUpper',
                        'options' => [
                            'encoding' => 'utf-8'
                        ]
                    ]
                ]
            ],

            'via' => [
                'name' => 'via',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'Zend\Filter\StringToUpper',
                        'options' => [
                            'encoding' => 'utf-8'
                        ]
                    ]
                ]
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
        if ($this->edit) {
            $spec['newligneId'] = [
                'name' => 'newligneId',
                'required' => true
            ];
        }
        return $spec;
    }

    public function modifFormForEdit()
    {
        $this->edit = true;
        $this->remove('ligneId');
        $this->add([
            'name' => 'ligneId',
            'type' => 'hidden'
        ])->add(
            [
                'name' => 'newligneId',
                'type' => 'text',
                'attributes' => [
                    'id' => 'ligne-ligneid',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Code de la ligne',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        return $this;
    }

    public function setData($data)
    {
        if (! array_key_exists('millesime', $data)) {
            $data['millesime'] = Session::get('millesime');
        }
        parent::setData($data);
        if ($this->has('newligneId')) {
            $e = $this->get('newligneId');
            $e->setValue($this->get('ligneId')
                ->getValue());
        }
    }
}
