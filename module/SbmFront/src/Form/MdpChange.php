<?php
/**
 * Formulaire de changement de mot de passe
 *
 * L'utilisateur donne l'ancien mot de passe et deux fois le nouveau.
 * 
 * @project sbm
 * @package SbmFront/Form
 * @filesource MdpChange.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmFront\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class MdpChange extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('mdp');
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
                'name' => 'mdp_old',
                'type' => 'password',
                'attributes' => [
                    'id' => 'mdp-old',
                    'class' => 'sbm-mdp'
                ],
                'options' => [
                    'label' => 'Donnez votre mot de passe',
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
                'name' => 'mdp_new',
                'type' => 'password',
                'attributes' => [
                    'id' => 'mdp-new',
                    'class' => 'sbm-mdp'
                ],
                'options' => [
                    'label' => 'Donnez un nouveau mot de passe',
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
                'name' => 'mdp_ctrl',
                'type' => 'password',
                'attributes' => [
                    'id' => 'mdp-ctrl',
                    'class' => 'sbm-mdp'
                ],
                'options' => [
                    'label' => 'Confirmez ce mot de passe',
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
                    'value' => 'Envoyer la demande',
                    'id' => 'responsable-submit',
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
                    'id' => 'responsable-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'mdp_new' => [
                'name' => 'mdp_new',
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
            'mdp_ctrl' => [
                'name' => 'mdp_ctrl',
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
                            'token' => 'mdp_new'
                        ]
                    ]
                ]
            ]
        ];
    }
}