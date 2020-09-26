<?php
/**
 * Formulaire des critères de recherche des élèves pour le portail
 *
 * @project sbm
 * @package SbmPortail/Form
 * @filesource CriteresForm.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmPortail\Form;

use SbmCommun\Form\CriteresForm as SbmCommunCriteresForm;
use Zend\InputFilter\InputFilterProviderInterface;

class CriteresForm extends SbmCommunCriteresForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('criteres');
        $this->setAttribute('method', 'post');

        $this->add(
            [
                'type' => 'text',
                'name' => 'numero',
                'attributes' => [
                    'id' => 'critere-nom',
                    'maxlength' => '11',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Numéro',
                    'label_attributes' => [
                        'class' => 'sbm-first'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'nomSA',
                'attributes' => [
                    'id' => 'critere-nom',
                    'maxlength' => '45',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Nom',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'prenomSA',
                'attributes' => [
                    'id' => 'critere-prenom',
                    'maxlength' => '45',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Prénom',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'responsable',
                'attributes' => [
                    'id' => 'critere-responsable',
                    'maxlength' => '45',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Responsable',
                    'label_attributes' => [
                        'class' => 'sbm-new-line'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'etablissementId',
                'attributes' => [
                    'id' => 'critere-etablissementId',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Etablissement',
                    'empty_option' => 'Tout',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'classeId',
                'attributes' => [
                    'id' => 'critere-classeId',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Classe',
                    'empty_option' => 'Tout',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'serviceId',
                'attributes' => [
                    'id' => 'critere-serviceId',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Circuit',
                    'label_attributes' => [
                        'class' => 'sbm-new-line'
                    ],
                    'empty_option' => 'Tous',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'stationId',
                'attributes' => [
                    'id' => 'critere-stationId',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Arrêt',
                    'empty_option' => 'Tous',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'submit',
                'name' => 'submit',
                'attributes' => [
                    'title' => 'Rechercher',
                    'id' => 'criteres-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'fam-find button submit'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'numero' => [
                'name' => 'numero',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'nomSA' => [
                'name' => 'nomSA',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'prenomSA' => [
                'name' => 'prenomSA',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'responsable' => [
                'name' => 'responsable',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'etablissementId' => [
                'name' => 'etablissementId',
                'required' => false
            ],
            'classeId' => [
                'name' => 'classeId',
                'required' => false
            ],
            'serviceId' => [
                'name' => 'serviceId',
                'required' => false
            ],
            'stationId' => [
                'name' => 'stationId',
                'required' => false
            ],
            'etat' => [
                'name' => 'etat',
                'required' => false
            ]
        ];
    }
}