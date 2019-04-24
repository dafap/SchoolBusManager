<?php
/**
 * Formulaire de saisie et modification d'un transporteur
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Transporteur.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Transporteur extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('transporteur');
        $this->setAttribute('method', 'post');
        $this->add([
            'name' => 'transporteurId',
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
                    'id' => 'transporteur-nom',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-50c'
                ],
                'options' => [
                    'label' => 'Nom du transporteur',
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
                    'id' => 'transporteur-adresseL1',
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
                    'id' => 'transporteur-adresseL2',
                    'class' => 'sbm-width-50c'
                ],
                'options' => [
                    'label' => 'Adresse (suite)',
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
                    'id' => 'transporteur-codepostal',
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
                    'id' => 'transporteur-communeId',
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
                    'id' => 'transporteur-telephone',
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
                    'id' => 'transporteur-fax',
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
                'type' => 'Zend\Form\Element\Email',
                'attributes' => [
                    'id' => 'transporteur-email',
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
                    'id' => 'transporteur-siret',
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
                    'id' => 'transporteur-naf',
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
                    'id' => 'transporteur-tvaIntraCommunautaire',
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
                'name' => 'rib_titulaire',
                'type' => 'text',
                'attributes' => [
                    'id' => 'transporteur-rib_titulaire',
                    'class' => 'sbm-width-35c'
                ],
                'options' => [
                    'label' => 'RIB - titulaire',
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
                'name' => 'rib_domiciliation',
                'type' => 'text',
                'attributes' => [
                    'id' => 'transporteur-rib_domiciliation',
                    'class' => 'sbm-width-35c'
                ],
                'options' => [
                    'label' => 'RIB - domiciliation',
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
                'name' => 'rib_bic',
                'type' => 'text',
                'attributes' => [
                    'id' => 'transporteur-rib_bic',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'RIB - BIC',
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
                'name' => 'rib_iban',
                'type' => 'text',
                'attributes' => [
                    'id' => 'transporteur-rib_iban',
                    'class' => 'sbm-width-35c'
                ],
                'options' => [
                    'label' => 'RIB - IBAN',
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
                    'id' => 'transporteur-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'transporteur-cancel',
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