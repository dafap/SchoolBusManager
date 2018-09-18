<?php
/**
 * Formulaire de saisie du mot de passe initial
 *
 * L'utilisateur frappe deux fois le mot de passe de son choix.
 * 
 * @project sbm
 * @package SbmFront/Form
 * @filesource MdpFirst.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmFront\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class MdpFirst extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('mdp-first');
        $this->setAttribute('method', 'post');
        $this->add([
            'name' => 'userId',
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
                'name' => 'mdp',
                'type' => 'password',
                'attributes' => [
                    'id' => 'mdp-new',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-mdp'
                ],
                'options' => [
                    'label' => 'Choisissez un mot de passe',
                    'label_attributes' => [
                        'class' => 'sbm-label-200px'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'ctrl',
                'type' => 'password',
                'attributes' => [
                    'id' => 'ctrl',
                    'class' => 'sbm-mdp'
                ],
                'options' => [
                    'label' => 'Confirmez ce mot de passe',
                    'label_attributes' => [
                        'class' => 'sbm-label-200px'
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
                    'id' => 'responsable-submit',
                    'class' => 'button submit left-95px'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'responsable-cancel',
                    'class' => 'button cancel left-10px'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'mdp' => [
                'name' => 'mdp',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'SbmFront\Model\Validator\Mdp',
                        'options' => [
                            'len' => 6,
                            'min' => 1,
                            'maj' => 1,
                            'num' => 1
                        ]
                    ]
                ]
            ],
            'ctrl' => [
                'name' => 'ctrl',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'identical',
                        'options' => [
                            'token' => 'mdp'
                        ]
                    ]
                ]
            ]
        ];
    }
}