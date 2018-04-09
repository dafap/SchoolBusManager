<?php
/**
 * Formulaire de choix du payeur (Famille, Gratuit ou Organisme)
 *
 * Le champ `organismeId` du formulaire n'est activé que si le bouton radio 
 * du champ `gratuit` est sur Organisme.
 * 
 * @project sbm
 * @package SbmGestion/Form/Eleve
 * @filesource PriseEnChargePaiement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmGestion\Form\Eleve;

use SbmCommun\Form\AbstractSbmForm as Form;
use Zend\InputFilter\InputFilterProviderInterface;

class PriseEnChargePaiement extends Form implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('formpaiement');
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
                'type' => 'hidden',
                'name' => 'eleveId'
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'gratuit',
                'attributes' => [],
                'options' => [
                    'label' => 'Choisissez le mode de prise en charge du paiement',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ],
                    'value_options' => [
                        [
                            'value' => '0',
                            'label' => 'Famille',
                            'attributes' => [
                                'id' => 'gratuitradio0',
                                'checked' => 'checked'
                            ]
                        ],
                        [
                            'value' => '1',
                            'label' => 'Gratuit',
                            'attributes' => [
                                'id' => 'gratuitradio1'
                            ]
                        ],
                        [
                            'value' => '2',
                            'label' => 'Organisme',
                            'attributes' => [
                                'id' => 'gratuitradio2'
                            ]
                        ]
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'organismeId',
                'attributes' => [
                    'id' => 'scolarites-organismeId',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Organisme payeur',
                    'label_attributes' => [
                        'class' => 'sbm-form-auto'
                    ],
                    'empty_option' => 'Choisissez dans la liste',
                    'allow_empty' => true,
                    'disable_inarray_validator' => false,
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
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'organismeId' => [
                'name' => 'organismeId',
                'required' => false
            ]
        ];
    }
}