<?php
/**
 * Formulaire de saisie et modification d'un paiement
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Paiement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 avr. 2018
 * @version 2018-2.4.1
 */
namespace SbmCommun\Form;

use Zend\Filter\StringToUpper;
use Zend\Filter\StripTags;
use Zend\Filter\StringTrim;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\FormInterface;

class Paiement extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     * Permet de passer les arguments à la méthode getInputFilter()
     *
     * @var array
     */
    private $args = [];

    public function __construct($args = ['responsableId' => true, 'note' => false], $param = 'paiement')
    {
        parent::__construct($param);
        $this->args = $args;
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
                'name' => 'paiementId',
                'type' => 'hidden'
            ]);
        
        $this->adapte($args);
        
        $this->add(
            [
                'name' => 'dateDepot',
                'type' => 'SbmCommun\Form\Element\DateTimeSelect',
                'attributes' => [
                    'id' => 'paiement-dateDepot'
                ],
                'options' => [
                    'label' => 'Date du dépot',
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
                'name' => 'datePaiement',
                'type' => 'Zend\Form\Element\DateTimeSelect',
                'attributes' => [
                    'id' => 'paiement-datePaiement'
                ],
                'options' => [
                    'label' => 'Date du paiement',
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
                'name' => 'dateValeur',
                'type' => 'Zend\Form\Element\DateSelect',
                'attributes' => [
                    'id' => 'paiement-dateValeur'
                ],
                'options' => [
                    'label' => 'Date de valeur',
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
                    'label' => 'Exercice budgétaire',
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
                    'label' => 'Mode de paiemant',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'value_options' => [],
                    'empty_option' => 'Choisissez le mode de paiement',
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
                'name' => 'banque',
                'type' => 'text',
                'attributes' => [
                    'id' => 'paiement-banque',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Banque',
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
                'name' => 'titulaire',
                'type' => 'text',
                'attributes' => [
                    'id' => 'paiement-titulaire',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Titulaire',
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
                'name' => 'reference',
                'type' => 'text',
                'attributes' => [
                    'id' => 'paiement-reference',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Référence du paiement',
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
                    'id' => 'paiement-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'paiement-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    /**
     * Attention, cette adaptation du formulaire doit être appelée dans le constructeur afin qu'on retrouve la valeur donnée
     * par la méthode $this->getData()
     *
     * @param bool $hidden            
     */
    private function adapte($config)
    {
        if ($config['responsableId']) {
            $this->add(
                [
                    'name' => 'responsableId',
                    'type' => 'hidden',
                    'attributes' => [
                        'id' => 'responsableId'
                    ]
                ]);
        } else {
            $this->add(
                [
                    'name' => 'responsableId',
                    'type' => 'Zend\Form\Element\Select',
                    'attributes' => [
                        'id' => 'responsableId',
                        'autofocus' => 'autofocus',
                        'class' => 'sbm-width-30c'
                    ],
                    'options' => [
                        'label' => 'Responsable',
                        'label_attributes' => [
                            'class' => 'sbm-label'
                        ],
                        'value_options' => [],
                        'empty_option' => 'Choisissez le responsable concerné',
                        'error_attributes' => [
                            'class' => 'sbm-error'
                        ]
                    ]
                ]);
        }
        if ($config['note']) {
            $this->add(
                [
                    'type' => 'textarea',
                    'name' => 'note',
                    'attributes' => [
                        'id' => 'note',
                        'class' => 'sbm-width-55c'
                    ],
                    'options' => [
                        'label' => 'Motif de la modification',
                        'label_attributes' => [
                            'class' => 'sbm-label-top'
                        ],
                        'error_attributes' => [
                            'class' => 'sbm-error'
                        ]
                    ]
                ]);
        }
    }

    public function getInputFilterSpecification()
    {
        $result = [
            'banque' => [
                'name' => 'banque',
                'required' => false
            ],
            'titulaire' => [
                'name' => 'titulaire',
                'required' => false
            ],
            'reference' => [
                'name' => 'reference',
                'required' => false
            ],
            'montant' => [
                'name' => 'montant',
                'required' => true,
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
                    ],
                    [
                        'name' => 'Zend\Validator\GreaterThan',
                        'options' => [
                            'min' => 0,
                            'inclusive' => false
                        ]
                    ]
                ]
            ]
        ];
        if (\array_key_exists('note', $this->args) && $this->args['note']) {
            $result['note'] = [
                'name' => 'note',
                'required' => true
            ];
        }
        return $result;
    }

    public function isValid()
    {
        $data = $this->data;
        if (empty($data['dateValeur']['year']) && empty($data['dateValeur']['month']) &&
             empty($data['dateValeur']['day'])) {
            $data['dateValeur']['year'] = $data['datePaiement']['year'];
            $data['dateValeur']['month'] = $data['datePaiement']['month'];
            $data['dateValeur']['day'] = $data['datePaiement']['day'];
        }
        $this->setData($data);
        return parent::isValid();
    }
}