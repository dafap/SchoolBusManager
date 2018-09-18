<?php
/**
 * Formulaire de login de la page d'accueil
 *
 * Compatible ZF3
 * 
 * @project sbm
 * @package SbmFront/Form
 * @filesource Login.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmFront\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class Login extends AbstractSbmForm implements InputFilterProviderInterface
{

    private $canonic_name;

    private $db_adapter;

    public function __construct($canonic_name, $db_adapter)
    {
        $this->canonic_name = $canonic_name;
        $this->db_adapter = $db_adapter;
        parent::__construct('login');
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'name' => 'email',
                'type' => 'Zend\Form\Element\Email',
                'attributes' => [
                    'id' => 'front-email',
                    'class' => 'sbm-page1',
                    'autofocus' => 'autofocus'
                ],
                'options' => [
                    'label' => 'Email',
                    'label_attributes' => [
                        'class' => 'sbm-label-page1'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'mdp',
                'type' => 'password',
                'attributes' => [
                    'id' => 'front-mdp',
                    'class' => 'sbm-page1'
                ],
                'options' => [
                    'label' => 'Mot de passe',
                    'label_attributes' => [
                        'class' => 'sbm-label-page1'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'signin',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Connexion',
                    'id' => 'sbm-signin',
                    'class' => 'default sbm-signin'
                ]
            ]);
        $this->add(
            [
                'name' => 'signup',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Créer un compte',
                    'id' => 'sbm-signup',
                    'class' => 'default sbm-signup'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'email' => [
                'name' => 'email',
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
                        'name' => 'Zend\Validator\EmailAddress'
                    ],
                    [
                        'name' => 'Zend\Validator\Db\RecordExists',
                        'options' => [
                            'table' => $this->canonic_name,
                            'field' => 'email',
                            'adapter' => $this->db_adapter
                        ]
                    ]
                ]
            ],
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
                ]
            ]
        ];
    }
}
 