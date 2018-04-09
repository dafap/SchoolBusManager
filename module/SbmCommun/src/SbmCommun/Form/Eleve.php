<?php
/**
 * Formulaire de saisie et modification d'un élève
 *
 * CE FORMULAIRE N'EST PAS UTILISE
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Eleve.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Form;

class Eleve extends AbstractSbmForm
{

    public function __construct()
    {
        parent::__construct('eleve');
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
                'name' => 'eleveId',
                'type' => 'hidden'
            ]);
        $this->add(
            [
                'name' => 'selection',
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => [
                    'id' => 'eleve-selection'
                ],
                'options' => [
                    'label' => 'Sélectionné',
                    'label_attributes' => [
                        'class' => 'sbm-label170'
                    ],
                    'use_hidden_element' => true,
                    'checked_value' => '1',
                    'unchecked_value' => '0'
                ]
            ]);
        $this->add(
            [
                'name' => 'nom',
                'type' => 'text',
                'attributes' => [
                    'id' => 'eleve-nom',
                    'class' => 'sbm-text30'
                ],
                'options' => [
                    'label' => 'Nom de l\'élève',
                    'label_attributes' => [
                        'class' => 'sbm-label170'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'prenom',
                'type' => 'text',
                'attributes' => [
                    'id' => 'eleve-prenom',
                    'class' => 'sbm-text30'
                ],
                'options' => [
                    'label' => 'Prénom de l\'élève',
                    'label_attributes' => [
                        'class' => 'sbm-label170'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'dateN',
                'type' => 'Zend\Form\Element\Date',
                'attributes' => [
                    'id' => 'eleve-dateN',
                    'class' => 'sbm-text15'
                ],
                'options' => [
                    'label' => 'Date de naissance',
                    'label_attributes' => [
                        'class' => 'sbm-label170'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        
        $this->add(
            [
                'name' => 'responsable1Id',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'eleve-responsable1Id',
                    'class' => 'sbm-select4'
                ],
                'options' => [
                    'label' => 'Responsable n°1',
                    'label_attributes' => [
                        'class' => 'sbm-label130'
                    ],
                    'empty_option' => 'Choisissez un responsable',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        
        $this->add(
            [
                'name' => 'responsable2Id',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'eleve-responsable2Id',
                    'class' => 'sbm-select4'
                ],
                'options' => [
                    'label' => 'Responsable n°2',
                    'label_attributes' => [
                        'class' => 'sbm-label150'
                    ],
                    'empty_option' => 'Choisissez un responsable',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        
        $this->add(
            [
                'name' => 'responsableFId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'eleve-responsableFId',
                    'class' => 'sbm-select4'
                ],
                'options' => [
                    'label' => 'Responsable financier',
                    'label_attributes' => [
                        'class' => 'sbm-label150'
                    ],
                    'empty_option' => 'Choisissez un responsable',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        
        $this->add(
            [
                'name' => 'note',
                'type' => 'Zend\Form\Element\Textarea',
                'attributes' => [
                    'id' => 'eleve-note',
                    'class' => 'sbm-note'
                ],
                'options' => [
                    'label' => 'Notes',
                    'label_attributes' => [
                        'class' => 'sbm-label130'
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
                    'id' => 'eleve-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'button submit left135'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'eleve-cancel',
                    'class' => 'button cancel'
                ]
            ]);
        
        $this->getInputFilter()
            ->get('responsable2Id')
            ->setRequired(false);
        // $this->getInputFilter()->get('responsableFId')->setRequired(false);
    }
}