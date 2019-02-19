<?php
/**
 * Formulaire de saisie d'un user
 * 
 * @project sbm
 * @package SbmAdmin/Form
 * @filesource User.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr]
 * @date 9 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmAdmin\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class User extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     *
     * @var string
     */
    private $canonical_name;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $db_adapter;

    /**
     *
     * @var int
     */
    private $userId;

    public function __construct($canonical_name, $db_adapter)
    {
        $this->canonical_name = $canonical_name;
        $this->db_adapter = $db_adapter;
        parent::__construct('compte');
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
                'name' => 'titre',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'user-titre',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Votre identité',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'value_options' => [
                        'M.' => 'Monsieur',
                        'Mme' => 'Madame',
                        'Mlle' => 'Mademoiselle',
                        'Dr' => 'Docteur',
                        'Me' => 'Maître',
                        'Pr' => 'Professeur'
                    ],
                    'empty_option' => 'Choisissez la civilité',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'nom',
                'type' => 'SbmCommun\Form\Element\NomPropre',
                'attributes' => [
                    'id' => 'user-nom',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Nom',
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
                'name' => 'prenom',
                'type' => 'SbmCommun\Form\Element\Prenom',
                'attributes' => [
                    'id' => 'user-prenom',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Prénom',
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
                'name' => 'email',
                'type' => 'Zend\Form\Element\Email',
                'attributes' => [
                    'id' => 'user-email',
                    'class' => 'sbm-width-55c'
                ],
                'options' => [
                    'label' => 'Email',
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
                'name' => 'categorieId',
                'attributes' => [
                    'id' => 'user-categorieId'
                ],
                'options' => [
                    'label' => 'Catégorie',
                    'label-attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Quelle catégorie ?',
                    'value_options' => [
                        '1' => 'Parent',
                        '2' => 'Transporteur',
                        '3' => 'Etablissement scolaire',
                        '200' => 'Secrétariat',
                        '253' => 'Gestionnaire',
                        '254' => 'Administrateur'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'tokenalive',
                'attributes' => [
                    'id' => 'user-tokenalive'
                ],
                'options' => [
                    'label' => 'Mot de passe bloqué',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'confirme',
                'attributes' => [
                    'id' => 'user-confirme'
                ],
                'options' => [
                    'label' => 'Confirmé',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'active',
                'attributes' => [
                    'id' => 'user-active'
                ],
                'options' => [
                    'label' => 'Activé',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'selection',
                'attributes' => [
                    'id' => 'user-selection'
                ],
                'options' => [
                    'label' => 'Sélectionné',
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
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'note',
                'attributes' => [
                    'id' => 'user-note',
                    'class' => 'sbm-note'
                ],
                'options' => [
                    'label' => 'Commentaires',
                    'label_attributes' => [],
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
                    'id' => 'responsable-cancel',
                    'class' => 'button default cancel left-10px'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'titre' => [
                'name' => 'titre',
                'required' => true
            ],
            'nom' => [
                'name' => 'nom',
                'required' => true
            ],
            'prenom' => [
                'name' => 'prenom',
                'required' => true
            ],
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
                        'name' => 'Zend\Validator\Db\NoRecordExists',
                        'options' => [
                            'table' => $this->canonical_name,
                            'field' => 'email',
                            'adapter' => $this->db_adapter,
                            'exclude' => [
                                'field' => 'userId',
                                'value' => $this->userId
                            ]
                        ]
                    ]
                ]
            ],
            'categorieId' => [
                'name' => 'categorieId',
                'required' => true
            ]
        ];
    }

    /**
     * Initialise la propriété userId pour le validateur avant d'appeler la méthode standard
     *
     * (non-PHPdoc]
     *
     * @see \Zend\Form\Form::setData(]
     */
    public function setData($data)
    {
        $this->userId = - 1;
        if (is_array($data)) {
            if (array_key_exists('userId', $data)) {
                $this->userId = $data['userId'];
            }
        } elseif ($data instanceof \ArrayAccess) {
            if ($data->offsetExists('userId')) {
                $this->userId = $data->offsetGet('userId');
            }
        } elseif ($data instanceof \Traversable) {
            foreach ($data as $key => $value) {
                if ($key == 'userId') {
                    $this->userId = $value;
                }
            }
        }
        return parent::setData($data);
    }
}