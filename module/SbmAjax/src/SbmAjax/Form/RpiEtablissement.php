<?php
/**
 * Formulaire d'ajout ou de suppression d'une école dans un RPI
 *
 * Appelé en ajax
 * 
 * @project sbm
 * @package SbmAjax/Form
 * @filesource RpiEtablissement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 juin 2018
 * @version 2018-2.4.1
 */
namespace SbmAjax\Form;

use SbmCommun\Form\AbstractSbmForm as Form;

class RpiEtablissement extends Form
{

    public function __construct($op = 'add')
    {
        parent::__construct('rpi-etablissement');
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
                    'name' => 'etablissementId',
                    'type' => 'Zend\Form\Element\Select',
                    'attributes' => [
                        'id' => 'rpi-etablissement-etablissementId',
                        'class' => 'sbm-width-45c'
                    ],
                    'options' => [
                        'label' => 'École',
                        'label_attributes' => [
                            'class' => 'sbm-form-auto'
                        ],
                        'empty_option' => 'Choisissez une école',
                        'error_attributes' => [
                            'class' => 'sbm-error'
                        ]
                    ]
                ]);
        } else {
            $this->add(
                [
                    'name' => 'etablissementId',
                    'type' => 'hidden'
                ]);
        }
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'decision-cancel',
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
                    'id' => 'decision-submit',
                    'class' => 'button default submit'
                ]
            ]);
    }
}