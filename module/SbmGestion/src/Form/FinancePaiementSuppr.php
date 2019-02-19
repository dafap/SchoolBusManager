<?php
/**
 * Formulaire de confirmation de la suppression d'un paiement.
 *
 * Lors de la suppression d'un paiement la raison est demandée et est obligatoire.
 * 
 * @project dbm
 * @package SbmGestion/Form
 * @filesource FinancePaiementSuppr.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmGestion\Form;

use SbmCommun\Form\AbstractSbmForm as Form;
use Zend\InputFilter\InputFilterProviderInterface;

class FinancePaiementSuppr extends Form implements InputFilterProviderInterface
{

    public function __construct($param = 'finance-paiement-suppr')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');

        $this->add([
            'name' => 'paiementId',
            'type' => 'hidden'
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
                    'label' => 'Motif de la suppression',
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
            'paiementId' => [
                'name' => 'paiementId',
                'required' => true
            ],
            'note' => [
                'name' => 'note',
                'required' => true
            ]
        ];
    }
}