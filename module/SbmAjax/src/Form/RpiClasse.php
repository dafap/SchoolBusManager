<?php
/**
 * Formulaire d'ajout ou de suppression d'une classe dans un établissement d'un RPI
 *
 * Appelé en ajax
 * 
 * @project sbm
 * @package SbmAjax/Form
 * @filesource RpiClasse.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmAjax\Form;

use SbmCommun\Form\AbstractSbmForm as Form;

class RpiClasse extends Form
{

    public function __construct($op = 'add')
    {
        parent::__construct('rpiclasse-form');
        $this->setAttribute('method', 'post');
        $this->add([
            'name' => 'etablissementId',
            'type' => 'hidden'
        ]);
        $this->add([
            'name' => 'op',
            'type' => 'hidden'
        ]);
        $this->add([
            'name' => 'niveau',
            'type' => 'hidden'
        ]);
        if ($op == 'add') {
            $this->add(
                [
                    'name' => 'classeId',
                    'type' => 'Zend\Form\Element\Select',
                    'attributes' => [
                        'id' => 'rpiclasse-classeId',
                        'class' => 'sbm-width-45c'
                    ],
                    'options' => [
                        'label' => 'Classe',
                        'label_attributes' => [
                            'class' => 'sbm-form-auto'
                        ],
                        'empty_option' => 'Choisissez une classe',
                        'error_attributes' => [
                            'class' => 'sbm-error'
                        ]
                    ]
                ]);
        } else {
            $this->add([
                'name' => 'classeId',
                'type' => 'hidden'
            ]);
        }
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'rpiclasse-cancel',
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
                    'id' => 'rpiclasse-submit',
                    'class' => 'button default submit'
                ]
            ]);
    }
}