<?php
/**
 * Formulaire de gestion des rpi
 *
 * @project sbm
 * @package SbmCommun/Form
 * @filesource Rpi.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Rpi extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('rpi');
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'name' => 'rpiId',
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
                'name' => 'nom',
                'type' => 'text',
                'attributes' => [
                    'id' => 'rpi-nom',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Code du RPI',
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
                'name' => 'libelle',
                'type' => 'text',
                'attributes' => [
                    'id' => 'rpi-libelle',
                    'class' => 'sbm-width-50c'
                ],
                'options' => [
                    'label' => 'Libellé',
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
                'type' => 'Zend\Form\Element\Select',
                'name' => 'grille',
                'attributes' => [
                    'id' => 'rpi-grille',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Grille tarifaire',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez la grille',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'name' => 'niveau',
                'attributes' => [
                    'id' => 'classe-niveau',
                    'class' => 'sbm-multicheckbox'
                ],
                'options' => [
                    'label' => 'Cochez les niveaux concernés',
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
                    'id' => 'classe-submit',
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
                    'id' => 'classe-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'nom' => [
                'name' => 'nom',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'libelle' => [
                'name' => 'libelle',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ]
        ];
    }
}