<?php
/**
 * Formulaire de choix d'un exercice
 *
 * @project sbm
 * @package SbmGestion/src/Form/Finances
 * @filesource ChoixExercice.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 juil. 2019
 * @version 2019-2.4.9
 */

namespace SbmGestion\Form\Finances;

use SbmCommun\Form\AbstractSbmForm as Form;

class ChoixExercice extends Form
{

    public function __construct($param = 'finance-choix-exercice')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');

        $this->add([
            'name'=>'exercice',
            'type'=>\Zend\Form\Element\Select::class,
            'attributes'=>[],
            'options'=>[
                'label' => 'Quel exercice ?',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option'=>'Choisissez',
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
                    'id' => 'finance-choix-exercice-submit',
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
                    'id' => 'finance-choix-exercice-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }
}