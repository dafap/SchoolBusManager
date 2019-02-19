<?php
/**
 * Formulaire d'ajout ou de suppression d'une commune dans un RPI
 *
 * Appelé en ajax
 * 
 * @project sbm
 * @package SbmAjax/Form
 * @filesource RpiCommune.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 fév. 2019
 * @version 2019-2.4.7
 */
namespace SbmAjax\Form;

use SbmCommun\Form\AbstractSbmForm as Form;

class RpiCommune extends Form
{

    public function __construct($op = 'add')
    {
        parent::__construct('rpicommune-form');
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'name' => 'rpiId',
                'type' => 'hidden'
            ]);
        $this->add(
            [
                'name' => 'op',
                'type' => 'hidden'
            ]);
        if ($op == 'add') {
            $this->add(
                [
                    'name' => 'communeId',
                    'type' => 'Zend\Form\Element\Select',
                    'attributes' => [
                        'id' => 'rpicommune-communeId',
                        'class' => 'sbm-width-45c'
                    ],
                    'options' => [
                        'label' => 'Commune',
                        'label_attributes' => [
                            'class' => 'sbm-form-auto'
                        ],
                        'empty_option' => 'Choisissez une commune',
                        'error_attributes' => [
                            'class' => 'sbm-error'
                        ]
                    ]
                ]);
        } else {
            $this->add(
                [
                    'name' => 'communeId',
                    'type' => 'hidden'
                ]);
        }
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'rpicommune-cancel',
                    'autofocus' => 'autofocus',
                    'class' => 'button default cancel'
                ]
            ]);
        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => $op == 'add' ? 'Ajouter' : 'Supprimer',
                    'id' => 'rpicommune-submit',
                    'class' => 'button default submit'
                ]
            ]);
    }
}