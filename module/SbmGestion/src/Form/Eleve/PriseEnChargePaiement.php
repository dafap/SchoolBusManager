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
 * @date 16 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Form\Eleve;

use SbmCommun\Form\AbstractSbmForm as Form;
use Zend\InputFilter\InputFilterProviderInterface;

class PriseEnChargePaiement extends Form implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('priseenchargepaiement-form');
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
                                'id' => 'priseenchargepaiement-gratuitradio0',
                                'checked' => 'checked'
                            ]
                        ],
                        [
                            'value' => '1',
                            'label' => 'Gratuit',
                            'attributes' => [
                                'id' => 'priseenchargepaiement-gratuitradio1'
                            ]
                        ],
                        [
                            'value' => '2',
                            'label' => 'Organisme',
                            'attributes' => [
                                'id' => 'priseenchargepaiement-gratuitradio2'
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
                    'id' => 'priseenchargepaiement-organismeId',
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
                    'id' => 'priseenchargepaiement-submit',
                    'value' => 'Enregistrer',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'id' => 'priseenchargepaiement-cancel',
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

    public function isValid()
    {
        if ($this->data['gratuit'] != 2) {
            $this->data['organismeId'] = null;
        } elseif (empty($this->data['organismeId'])) {
            $this->get('organismeId')->setMessages(
                [
                    "Il faut choisir l'organisme payeur."
                ]);
            return false;
        }
        return parent::isValid();
    }
}