<?php
/**
 * Formulaire de confirmation de la résiliation de paiements d'un responsable.
 *
 * Lors de la résiliation de paiements la raison est demandée et est obligatoire.
 *
 * @project dbm
 * @package SbmGestion/Form/Finances
 * @filesource FinancePaiementResiliation.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 juil. 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Form\Finances;

use SbmCommun\Form\AbstractSbmForm as Form;
use Zend\InputFilter\InputFilterProviderInterface;

class FinancePaiementResiliation extends Form implements InputFilterProviderInterface
{

    public function __construct($param = 'finance-paiement-suppr')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');

        $this->add([
            'name' => 'responsableId',
            'type' => 'hidden'
        ]);

        $this->add(
            [
                'name' => 'natures',
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'attributes' => [],
                'options' => [
                    'label' => 'Ces paiements concernent :',
                    'value_options' =>[
                        [
                            'value' => 'duplicata',
                            'attributes' => [
                                'id' => 'nature-duplicata'
                            ],
                            'label' => 'Des duplicatas',
                            'label_attributes'=>[
                                'class' => 'nature'
                            ]
                        ],
                        [
                            'value' => 'abonnement',
                            'attributes' => [
                                'id' => 'nature-abonnement'
                            ],
                            'label' => 'Des abonnements',
                            'label_attributes'=>[
                                'class' => 'nature'
                            ]
                        ]
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);

        $this->add([
            'name'=>'eleveIds',
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'attributes' => [],
            'options' => [
                'label' => 'Cochez les abonnements à supprimer :',
                'value_options' => []
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
                    'label' => 'Motif de la résiliation',
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
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Confirmer',
                    'id' => 'finance-paiement-suppr-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'finance-paiement-suppr-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'responsableId' => [
                'name' => 'responsableId',
                'required' => true
            ],
            'natures' => [
                'name' => 'natures',
                'required' => true
            ],
            'eleveIds' => [
                'name' => 'eleveIds',
                'required' => false
            ],
            'note' => [
                'name' => 'note',
                'required' => true
            ]
        ];
    }
}