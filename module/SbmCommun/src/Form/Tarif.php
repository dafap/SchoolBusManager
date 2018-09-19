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
 * @date 19 sept.2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Tarif extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('tarif');
        $this->setAttribute('method', 'post');
        $this->add([
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
                'type' => 'text',
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
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'rythme',
                'attributes' => [
                    'id' => 'tarif-rytme',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Rythme de paiement',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un rythme de paiement',
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
                    'empty_option' => 'Choisissez une grille',
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
                    'label' => 'Mode de paiement',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un mode de paiement',
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
                            'inclusive' => false
                        ]
                    ]
                ]
            ]
        ];
    }
}