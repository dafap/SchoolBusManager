<?php
/**
 * Formulaire de saisie et modificationd d'un tarif
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Tarif
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Tarif extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('tarif');
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'name' => 'tarifId',
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
                'name' => 'nom',
                'type' => 'text',
                'attributes' => [
                    'id' => 'tarif-nom',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-50c'
                ],
                'options' => [
                    'label' => 'Libellé du tarif',
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
                    'id' => 'tarif-montant',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Montant',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add([
            'name' => 'duplicata',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => [
                'id' => 'tarif-duplicata',
                'class' => 'sbm-checkbox',
            ],
            'options' => [
                'label' => 'Duplicata',
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
                'name' => 'grille',
                'attributes' => [
                    'id' => 'tarif-grille',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Grille tarifaire',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez la grille',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add([
            'name' => 'reduit',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => [
                'id' => 'tarif-reduit',
                'class' => 'sbm-checkbox',
            ],
            'options' => [
                'label' => 'Réduit',
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
                'name' => 'mode',
                'attributes' => [
                    'id' => 'tarif-mode',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Mode de calcul',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez le mode de calcul',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'seuil',
                'type' => 'SbmCommun\Form\Element\IsInt',
                'attributes' => [
                    'id' => 'tarif-seuil',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Seuil',
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
                    'id' => 'tarif-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'tarif-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'nom' => [
                'name' => 'nom',
                'requeried' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
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
                            'inclusive' => true
                        ]
                    ]
                ]
            ]
        ];
    }
}