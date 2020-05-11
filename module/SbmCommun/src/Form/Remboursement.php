<?php
/**
 * Formulaire de saisie et modification d'un remboursement
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Remboursement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Remboursement extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct($name = 'remboursement', $options = [])
    {
        parent::__construct($name, $options);
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
            'name' => 'paiementId',
            'type' => 'hidden'
        ]);
        $this->add(
            [
                'name' => 'responsableId',
                'type' => 'hidden',
                'attributes' => [
                    'id' => 'responsableId'
                ]
            ]);
        $this->add(
            [
                'name' => 'datePaiement',
                'type' => 'Zend\Form\Element\DateTimeSelect',
                'attributes' => [
                    'id' => 'paiement-datePaiement'
                ],
                'options' => [
                    'label' => 'Date du remboursement',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'create_empty_option' => true,
                    'min_year' => date('Y') - 10,
                    'max_year' => date('Y') + 1,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'anneeScolaire',
                'type' => 'text',
                'attributes' => [
                    'id' => 'paiement-annee-scolaire',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Année scolaire',
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
                'name' => 'exercice',
                'type' => 'text',
                'attributes' => [
                    'id' => 'paiement-exercice',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Année comptable',
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
                'name' => 'montant',
                'type' => 'SbmCommun\Form\Element\IsDecimal',
                'attributes' => [
                    'id' => 'paiement-montant',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Montant',
                    'label_attributes' => [
                        'class' => 'sbm-label170'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'codeModeDePaiement',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'paiement-mode-de-paiement',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Mode de remboursement',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'value_options' => [],
                    'empty_option' => 'Choisissez le mode de remboursement',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'codeCaisse',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'paiement-mode-caisse',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Caisse',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'value_options' => [],
                    'empty_option' => 'Choisissez la caisse',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'reference',
                'type' => 'text',
                'attributes' => [
                    'id' => 'paiement-reference',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Référence du remboursement',
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
                'type' => 'textarea',
                'name' => 'note',
                'attributes' => [
                    'id' => 'note',
                    'class' => 'sbm-width-55c'
                ],
                'options' => [
                    'label' => 'Motif du remboursement',
                    'label_attributes' => [
                        'class' => 'sbm-label-top'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'submit',
                'type' => 'submit',
                'attributes' => [
                    'value' => 'Enregistrer',
                    'id' => 'paiement-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'type' => 'submit',
                'attributes' => [
                    'value' => 'Abandonner',
                    'id' => 'paiement-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'anneeScolaire' => [
                'name' => 'anneeScolaire',
                'required' => true
            ],
            'exercice' => [
                'name' => 'exercice',
                'required' => true
            ],
            'reference' => [
                'name' => 'reference',
                'required' => false
            ],
            'montant' => [
                'name' => 'montant',
                'required' => true,
                'validators' => [
                    [
                        'name' => 'SbmCommun\Model\Validator\Decimal'
                    ],
                    [
                        'name' => 'Zend\Validator\GreaterThan',
                        'options' => [
                            'min' => 0,
                            'inclusive' => false
                        ]
                    ]
                ]
            ],
            'note' => [
                'name' => 'note',
                'required' => true
            ]
        ];
    }
}