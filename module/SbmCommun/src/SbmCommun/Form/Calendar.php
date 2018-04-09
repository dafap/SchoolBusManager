<?php
/**
 * Formulaire de saisie et modification d'un élément de `calendar`
 *
 * @project sbm
 * @package SbmCommun/Form
 * @filesource Calendar.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Calendar extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('calendar');
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'name' => 'calendarId',
                'type' => 'hidden'
            ]);
        $this->add(
            [
                'name' => 'millesime',
                'type' => 'hidden'
            ]);
        
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
                'name' => 'description',
                'type' => 'text',
                'attributes' => [
                    'id' => 'calendar-description',
                    'class' => 'sbm-width-55c'
                ],
                'options' => [
                    'label' => 'Description',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'dateDebut',
                'type' => 'Zend\Form\Element\DateSelect',
                'attributes' => [
                    'id' => 'calendar-dateDebut'
                ],
                'options' => [
                    'label' => 'Date de début',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'create_empty_option' => true,
                    'min_year' => date('Y') - 20,
                    'max_year' => date('Y') + 2,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'dateFin',
                'type' => 'Zend\Form\Element\DateSelect',
                'attributes' => [
                    'id' => 'calendar-dateFin'
                ],
                'options' => [
                    'label' => 'Date de fin',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'create_empty_option' => true,
                    'min_year' => date('Y') - 20,
                    'max_year' => date('Y') + 2,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'echeance',
                'type' => 'Zend\Form\Element\DateSelect',
                'attributes' => [
                    'id' => 'calendar-echeance'
                ],
                'options' => [
                    'label' => 'Echéance',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'create_empty_option' => true,
                    'min_year' => date('Y') - 20,
                    'max_year' => date('Y') + 2,
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'exercice',
                'type' => 'text',
                'attributes' => [
                    'id' => 'calendar-exercice',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Exercice budgétaire',
                    'label_attributes' => [
                        'class' => 'sbm-label'
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
                    'value' => 'Enregistrer',
                    'id' => 'calendar-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'button default submit left-95px'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'calendar-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'description' => [
                'name' => 'description',
                'required' => true
            ],
            'dateDebut' => [
                'name' => 'dateDebut',
                'required' => true
            ],
            'dateFin' => [
                'name' => 'dateFin',
                'required' => true
            ],
            'echeance' => [
                'name' => 'echeance',
                'required' => true
            ]
        ];
    }
}