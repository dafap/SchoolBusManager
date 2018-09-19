<?php
/**
 * Formulaire de saisie et modificationd d'une classe
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Classe.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 sept.2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Classe extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('classe');
        $this->setAttribute('method', 'post');
        $this->add([
            'name' => 'classeId',
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
                    'id' => 'classe-nom',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Nom de la classe',
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
                'name' => 'aliasCG',
                'type' => 'text',
                'attributes' => [
                    'id' => 'classe-aliascg',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Libellé complet',
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
                'type' => 'Zend\Form\Element\Select',
                'name' => 'suivantId',
                'attributes' => [
                    'id' => 'classe-suivantId'
                ],
                'options' => [
                    'label' => 'Indiquez la classe suivante',
                    'label_attributes' => [
                        'class' => 'sbm-label130'
                    ],
                    'empty_option' => 'Aucune',
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
            'aliasCG' => [
                'name' => 'aliasCG',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'suivantId' => [
                'name' => 'suivantId',
                'required' => false
            ]
        ];
    }
}