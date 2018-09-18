<?php
/**
 * Formulaire permettant de paramétrer la préparation d'une simulation
 * 
 * @project sbm
 * @package SbmGestion/Form
 * @filesource Simulation.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmGestion\Form;

use SbmCommun\Form\AbstractSbmForm as Form;
use Zend\InputFilter\InputFilterProviderInterface;

class Simulation extends Form implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('simulation');
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'name' => 'millesime',
                'type' => 'text',
                'attributes' => [
                    'id' => 'simulation_millesime',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Millésime de base',
                    'label_attributes' => [
                        'class' => 'sbm-label sbm-form-auto'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error sbm-form-auto'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'decision-cancel',
                    'autofocus' => 'autofocus',
                    'class' => 'button default cancel left-10px'
                ]
            ]);
        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Valider',
                    'id' => 'decision-submit',
                    'class' => 'button default submit left-10px'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'millesime' => [
                'name' => 'millesime',
                'required' => true
            ]
        ];
    }
} 