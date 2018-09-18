<?php
/**
 * Formulaire permettant de paramétrer la préparation d'un bordereau de remise de valeurs
 *
 * @project sbm
 * @package SbmGestion/Form/Finances
 * @filesource BordereauRemiseValeurCreer.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmGestion\Form\Finances;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class BordereauRemiseValeurCreer extends AbstractSbmForm implements 
    InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('bordereau');
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
                'type' => 'text',
                'name' => 'exercice',
                'attributes' => [
                    'id' => 'exercice'
                ],
                'options' => [
                    'label' => 'Exercice budgétaire',
                    'label_attributes' => [
                        'class' => 'sbm-label-140dem'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'anneeScolaire',
                'attributes' => [
                    'id' => 'anneeScolaire'
                ],
                'options' => [
                    'label' => 'Année scolaire',
                    'label_attributes' => [
                        'class' => 'sbm-label-140dem'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'codeModeDePaiement',
                'attributes' => [
                    'id' => 'codeModeDePaiement'
                ],
                'options' => [
                    'label' => 'Quel mode de paiement ?',
                    'label_attributes' => [
                        'class' => 'sbm-label-140dem'
                    ],
                    'empty_option' => 'Choisissez dans la liste',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'codeCaisse',
                'attributes' => [
                    'id' => 'codeCaisse'
                ],
                'options' => [
                    'label' => 'Dans quelle caisse ?',
                    'label_attributes' => [
                        'class' => 'sbm-label-140dem'
                    ],
                    'empty_option' => 'Choisissez dans la liste',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'preparer-cancel',
                    'autofocus' => 'autofocus',
                    'class' => 'button default cancel'
                ]
            ]);
        $this->add(
            [
                'name' => 'preparer',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Préparer un bordereau',
                    'id' => 'preparer-submit',
                    'class' => 'button default submit'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'exercice' => [
                'name' => 'exercice',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\Digits'
                    ]
                ]
            ],
            'anneeScolaire' => [
                'name' => 'anneeScolaire',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'SbmCommun\Filter\DigitSeparator'
                    ]
                ]
            ]
        ];
    }
}