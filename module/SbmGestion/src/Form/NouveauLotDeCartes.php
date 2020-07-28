<?php
/**
 * Formulaire permettant de créer un nouveau lot d'impression de cartes.
 *
 * @project sbm
 * @package SbmGestion/form
 * @filesource NouveauLotDeCartes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 juil. 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Form;

use SbmCommun\Form\AbstractSbmForm as Form;

class NouveauLotDeCartes extends Form
{

    public function __construct()
    {
        parent::__construct('decision');
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'name' => 'demande',
                'type' => 'Zend\Form\Element\Radio',
                'attributes' => [
                    'class' => 'sbm-radio',
                    'value' => '2'
                ],
                'options' => [
                    'label' => 'État des demandes à prendre en compte',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ],
                    'value_options' => [
                        '2' => 'Uniquement les demandes traitées',
                        '3' => 'Toutes les demandes'
                    ],
                    'error_attributes' => [
                        'class' => 'error_class'
                    ]
                ]
            ])->add(
            [
                'name' => 'nouvelle',
                'type' => 'submit',
                'attributes' => [
                    'id' => 'id',
                    'class' => 'button default submit left-95px',
                    'value' => 'Préparer une nouvelle édition'
                ]
            ]);
    }
}