<?php
/**
 * Formulaire de changement d'email
 *
 * Mot de passe et le nouvel email avec confirmation.
 * 
 * @project sbm
 * @package SbmFront/Form
 * @filesource EmailChange.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmFront\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class EmailChange extends AbstractSbmForm implements InputFilterProviderInterface
{

    private $canonic_name;

    private $db_adapter;

    public function __construct($canonic_name, $db_adapter)
    {
        $this->canonic_name = $canonic_name;
        $this->db_adapter = $db_adapter;
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
                'name' => 'mdp',
                'type' => 'password',
                'attributes' => [
                    'id' => 'mdp',
                    'class' => 'sbm-mdp',
                    'autofocus' => 'autofocus'
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
                'name' => 'email_new',
                'type' => 'Zend\Form\Element\Email',
                'attributes' => [
                    'id' => 'front-email',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Donnez votre nouvel email',
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
                'name' => 'email_ctrl',
                'type' => 'Zend\Form\Element\Email',
                'attributes' => [
                    'id' => 'front-email',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Confirmez cet email',
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
            'mdp' => [
                'name' => 'mdp',
                'required' => true
            ],
            'email_new' => [
                'name' => 'email_new',
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
                        'name' => 'Zend\Validator\Db\NoRecordExists',
                        'options' => [
                            'table' => $this->canonic_name,
                            'field' => 'email',
                            'adapter' => $this->db_adapter
                        ]
                    ]
                ]
            ],
            'email_ctrl' => [
                'name' => 'email_ctrl',
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
                            'token' => 'email_new'
                        ]
                    ]
                ]
            ]
        ];
    }
} 