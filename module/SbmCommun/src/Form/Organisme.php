<?php
/**
 * Formulaire de saisie et modification d'un organisme
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Organisme.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 sept.2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Organisme extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('organisme');
        $this->setAttribute('method', 'post');
        $this->add([
            'name' => 'organismeId',
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
                    'id' => 'organisme-nom',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-50c'
                ],
                'options' => [
                    'label' => 'Nom de l\'organisme',
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
                'name' => 'adresse1',
                'type' => 'text',
                'attributes' => [
                    'id' => 'organisme-adresseL1',
                    'class' => 'sbm-width-50c'
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
                'name' => 'adresse2',
                'type' => 'text',
                'attributes' => [
                    'id' => 'organisme-adresseL2',
                    'class' => 'sbm-width-50c'
                ],
                'options' => [
                    'label' => 'Adresse (suite]',
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
                'name' => 'codePostal',
                'type' => 'SbmCommun\Form\Element\CodePostal',
                'attributes' => [
                    'id' => 'organisme-codepostal',
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
                'name' => 'communeId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'organisme-communeId',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Commune',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez une commune',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'telephone',
                'type' => 'text',
                'attributes' => [
                    'id' => 'organisme-telephone',
                    'class' => 'sbm-width-15c'
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
                'name' => 'fax',
                'type' => 'text',
                'attributes' => [
                    'id' => 'organisme-fax',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Fax',
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
                'type' => 'text',
                'attributes' => [
                    'id' => 'organisme-email',
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
                'name' => 'siret',
                'type' => 'text',
                'attributes' => [
                    'id' => 'organisme-siret',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'SIRET',
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
                'name' => 'naf',
                'type' => 'text',
                'attributes' => [
                    'id' => 'organisme-naf',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'NAF',
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
                'name' => 'tvaIntraCommunautaire',
                'type' => 'text',
                'attributes' => [
                    'id' => 'organisme-tvaIntraCommunautaire',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'TVA intra communautaire',
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
                    'id' => 'organisme-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'organisme-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'nom' => [
                'name' => 'nom',
                'required' => true
            ],
            'codePostal' => [
                'name' => 'codePostal',
                'required' => true
            ],
            'communeId' => [
                'name' => 'communeId',
                'required' => true
            ],
            'telephone' => [
                'name' => 'telephone',
                'required' => true
            ],
            'email' => [
                'name' => 'email',
                'required' => true
            ]
        ];
    }
}