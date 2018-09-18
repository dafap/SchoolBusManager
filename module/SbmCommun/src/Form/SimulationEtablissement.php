<?php
/**
 * Formulaire de gestion des règles de correspondance pour le passage dans un 
 * établissement de niveau supérieur
 *
 * @project sbm
 * @package SbmCommun/Form
 * @filesource SimulationEtablissement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 août 2018
 * @version 2018-2.4.3
 */
namespace SbmCommun\Form;

class SimulationEtablissement extends AbstractSbmForm
{

    public function __construct()
    {
        parent::__construct('simulation-etablissement');
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
                'name' => 'origineId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'origineIdElement',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Établissement d\'origine',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un établissement',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'suivantId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'suivantIdElement',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Établissement suivant',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez un établissement',
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
                    'id' => 'simulation-etablissement-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'simulation-etablissement-cancel',
                    'class' => 'button default cancel left-10px'
                ]
            ]);
    }
}