<?php
/**
 * Formulaire des critères de recherche des élèves pour le portail des étabissements
 *
 * @project sbm
 * @package SbmPortail/src/Form
 * @filesource CriteresEtablissementForm.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2021
 * @version 2021-2.6.1
 */
namespace SbmPortail\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class CriteresEtablissementForm extends AbstractSbmForm implements
    InputFilterProviderInterface
{

    private $choixEtablissement;

    public function __construct(array $arrayEtablissements = [])
    {
        $this->choixEtablissement = count($arrayEtablissements) > 1;
        parent::__construct('criteres');
        $this->setAttribute('method', 'post');

        $this->add(
            [
                'type' => 'text',
                'name' => 'nomSA',
                'attributes' => [
                    'id' => 'critere-nom',
                    'maxlength' => '45',
                    'class' => 'sbm-width-25c'
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
                    'class' => 'sbm-width-25c'
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
                'type' => 'Zend\Form\Element\Select',
                'name' => 'regimeId',
                'attributes' => [
                    'id' => 'critere-regimeId',
                    'class' => 'sbm-width-7c'
                ],
                'options' => [
                    'label' => 'Régime',
                    'empty_option' => 'Tout',
                    'value_options' => [
                        'DP',
                        'Interne'
                    ],
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
                    'class' => 'sbm-width-25c'
                ],
                'options' => [
                    'label' => 'Responsable',
                    'label_attributes' => [],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'communeId',
                'attributes' => [
                    'id' => 'critere-communeId',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Commune',
                    'label_attributes' => [
                        'class' => 'sbm-new-line'
                    ],
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
        if ($this->choixEtablissement) {
            $this->add(
                [
                    'type' => 'Zend\Form\Element\Select',
                    'name' => 'etablissementId',
                    'attributes' => [
                        'id' => 'critere-etablissementId',
                        'class' => 'sbm-width-45c'
                    ],
                    'options' => [
                        'label' => 'Etablissement',
                        'label_attributes' => [],
                        'empty_option' => 'Tout',
                        'value_options' => $arrayEtablissements,
                        'error_attributes' => [
                            'class' => 'sbm-error'
                        ]
                    ]
                ]);
        }
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'serviceId',
                'attributes' => [
                    'id' => 'critere-serviceId',
                    'class' => 'sbm-width-15c'
                ],
                'options' => [
                    'label' => 'Service',
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
                'type' => 'text',
                'name' => 'numero',
                'attributes' => [
                    'id' => 'critere-nom',
                    'maxlength' => '11',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'PASS n°',
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
        $config = [
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
            'communeId' => [
                'name' => 'communeId',
                'required' => false
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
            'regimeId' => [
                'name' => 'regimeId',
                'required' => false
            ]
        ];
        if (! $this->choixEtablissement) {
            unset($config['etablissementId']);
        }
        return $config;
    }
}