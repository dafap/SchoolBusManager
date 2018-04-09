<?php
/**
 * Formulaire d'édition d'une liste de diffusion
 *
 * La méthode getDataForApi3 renvoie les données dans le format attendu par l'API version 3.0
 * La méthode setDataFromApi3 importe les données d'un format de l'API version 3.0
 * 
 * @project sbm
 * @package SbmMailChimp/Form
 * @filesource Liste.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmMailChimp\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\FormInterface;
use SbmBase\Model\StdLib;

class Liste extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('sbm-mailchimp-liste');
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'type' => 'hidden',
                'name' => 'id_liste'
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
                'name' => 'name',
                'type' => 'text',
                'attributes' => [
                    'id' => 'mailchimp-name',
                    'class' => 'sbm-width-45c'
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
                'name' => 'company',
                'type' => 'text',
                'attributes' => [
                    'id' => 'mailchimp-company',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Organisateur',
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
                'name' => 'address1',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'mailchimp-address1',
                    'class' => 'sbm-width-40c'
                ],
                'options' => [
                    'label' => 'Adresse',
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
                'name' => 'address2',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'mailchimp-address2',
                    'class' => 'sbm-width-40c'
                ],
                'options' => [
                    'label' => 'Complément d\'adresse',
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
                'name' => 'zip',
                'type' => 'SbmCommun\Form\Element\CodePostal',
                'attributes' => [
                    'id' => 'mailchimp-zip',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Code postal',
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
                'name' => 'city',
                'type' => 'text',
                'attributes' => [
                    'id' => 'mailchimp-city',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Commune',
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
                'name' => 'state',
                'type' => 'text',
                'attributes' => [
                    'id' => 'mailchimp-state',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Pays',
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
                'name' => 'country',
                'type' => 'text',
                'attributes' => [
                    'id' => 'mailchimp-country',
                    'class' => 'sbm-width-2c'
                ],
                'options' => [
                    'label' => 'Code du pays',
                    'label_attributes' => [
                        'value' => 'FR',
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'phone',
                'type' => 'SbmCommun\Form\Element\Telephone',
                'attributes' => [
                    'id' => 'mailchimp-phone',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Téléphone',
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
                'name' => 'permission_reminder',
                'type' => 'Zend\Form\Element\Textarea',
                'attributes' => [
                    'id' => 'mailchimp-permission-reminder',
                    'class' => 'sbm-width-50c'
                ],
                'options' => [
                    'label' => 'Rappel d\'autorisation',
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
                'name' => 'from_name',
                'type' => 'SbmCommun\Form\Element\NomPropre',
                'attributes' => [
                    'id' => 'mailchimp-from-name',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Nom',
                    'label_attributes' => [
                        'class' => 'sbm-label responsable-nom'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'from_email',
                'type' => 'Zend\Form\Element\Email',
                'attributes' => [
                    'id' => 'mailchimp-from-email',
                    'class' => 'sbm-width-50c'
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
                'name' => 'subject',
                'type' => 'text',
                'attributes' => [
                    'id' => 'mailchimp-subject',
                    'value' => 'Lettre d\'information de *|LIST:COMPANY|*',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Sujet',
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
                'name' => 'language',
                'type' => 'text',
                'attributes' => [
                    'id' => 'mailchimp-language',
                    'value' => 'fr',
                    'class' => 'sbm-width-2c'
                ],
                'options' => [
                    'label' => 'Langue',
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
                'name' => 'email_type_option',
                'type' => 'Zend\Form\Element\Radio',
                'attributes' => [
                    'id' => 'mailchimp-email-type-option',
                    'class' => 'sbm-radio'
                ],
                'options' => [
                    'label' => 'Type d\'email',
                    'label_attributes' => [
                        'class' => 'sbm-label-radio'
                    ],
                    'value_options' => [
                        '0' => 'Texte brut',
                        '1' => 'Html'
                    ]
                ]
            ]);
        
        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Enregistrer',
                    'id' => 'sbm-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'sbm-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'name' => [
                'name' => 'name',
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
            'company' => [
                'name' => 'company',
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
            'address1' => [
                'name' => 'address1',
                'required' => true
            ],
            'address2' => [
                'name' => 'address2',
                'required' => false
            ],
            'zip' => [
                'name' => 'zip',
                'required' => true
            ],
            'city' => [
                'name' => 'city',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'StringToUpper'
                    ]
                ]
            ],
            'state' => [
                'name' => 'state',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'StringToUpper'
                    ]
                ]
            ],
            'country' => [
                'name' => 'country',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'StringToUpper'
                    ]
                ]
            ],
            'phone' => [
                'name' => 'phone',
                'required' => false
            ],
            'permission_reminder' => [
                'name' => 'permission_reminder',
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
            'from_name' => [
                'name' => 'from_name',
                'required' => true
            ],
            'from_email' => [
                'name' => 'from_email',
                'required' => true
            ],
            'subject' => [
                'name' => 'subject',
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
            'language' => [
                'name' => 'language',
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

    /**
     * Fait un Form::getData() puis structure le résultat pour l'API v3
     *
     * @param boolean $with_id
     *            si vrai alors l'id de la liste est dans le résultat, sinon il n'y est pas
     * @param int $flag
     *            constante définié dans FormInterface
     *            
     * @return array tableau associatif structuré comme l'indique le résultat d'un appel par l'API v3
     */
    public function getDataForApi3($with_id = true, $flag = FormInterface::VALUES_NORMALIZED)
    {
        $data = $this->getData($flag);
        $result = [
            'id' => StdLib::getParam('id_liste', $data),
            'name' => StdLib::getParam('name', $data),
            'contact' => [
                'company' => StdLib::getParam('company', $data),
                'address1' => StdLib::getParam('address1', $data),
                'address2' => StdLib::getParam('address2', $data),
                'city' => StdLib::getParam('city', $data),
                'state' => StdLib::getParam('state', $data),
                'zip' => StdLib::getParam('zip', $data),
                'country' => StdLib::getParam('country', $data),
                'phone' => StdLib::getParam('phone', $data)
            ],
            'permission_reminder' => StdLib::getParam('permission_reminder', $data),
            'campaign_defaults' => [
                'from_name' => StdLib::getParam('from_name', $data),
                'from_email' => StdLib::getParam('from_email', $data),
                'subject' => StdLib::getParam('subject', $data),
                'language' => StdLib::getParam('language', $data)
            ],
            'email_type_option' => StdLib::getParam('email_type_option', $data) == 1,
            'visibility' => 'prv'
        ];
        if (! $with_id) {
            unset($result['id']);
        }
        return $result;
    }

    /**
     * Fait un Form::setData($data) à partir d'un tableau structuré selon l'API v3
     *
     * @param array $data
     *            tableau associatif structuré comme l'indique le résultat d'un appel par l'API v3
     * @param boolean $with_id
     *            si vrai alors l'id de la liste est mis dans les data, sinon il est laissé vide
     *            
     * @return \SbmMailChimp\Form\Liste
     */
    public function setDataFromApi3($data, $with_id = true)
    {
        $this->setData(
            [
                'id_liste' => $with_id ? StdLib::getParam('id', $data) : null,
                'name' => StdLib::getParam('name', $data),
                'company' => StdLib::getParamR(
                    [
                        'contact',
                        'company'
                    ], $data),
                'address1' => StdLib::getParamR(
                    [
                        'contact',
                        'address1'
                    ], $data),
                'address2' => StdLib::getParamR(
                    [
                        'contact',
                        'address2'
                    ], $data),
                'city' => StdLib::getParamR(
                    [
                        'contact',
                        'city'
                    ], $data),
                'state' => StdLib::getParamR(
                    [
                        'contact',
                        'state'
                    ], $data),
                'zip' => StdLib::getParamR(
                    [
                        'contact',
                        'zip'
                    ], $data),
                'country' => StdLib::getParamR(
                    [
                        'contact',
                        'country'
                    ], $data),
                'phone' => StdLib::getParamR(
                    [
                        'contact',
                        'phone'
                    ], $data),
                'permission_reminder' => StdLib::getParam('permission_reminder', $data),
                'from_name' => StdLib::getParamR(
                    [
                        'campaign_defaults',
                        'from_name'
                    ], $data),
                'from_email' => StdLib::getParamR(
                    [
                        'campaign_defaults',
                        'from_email'
                    ], $data),
                'subject' => StdLib::getParamR(
                    [
                        'campaign_defaults',
                        'subject'
                    ], $data),
                'language' => StdLib::getParamR(
                    [
                        'campaign_defaults',
                        'language'
                    ], $data),
                'email_type_option' => StdLib::getParam('email_type_option', $data)
            ]);
        return $this;
    }
}