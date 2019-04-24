<?php
/**
 * Formulaire des critères pour l'affichage des tables
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource CriteresForm.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class CriteresForm extends AbstractSbmForm implements InputFilterProviderInterface
{

    private $tableName;

    /**
     * Si tableName est un tableau, ce tableau décrit les éléménts à placer dans le
     * formulaire
     *
     * @param string|array $tableName
     */
    public function __construct($tableName = null)
    {
        $this->tableName = is_array($tableName) ? 'generic' : $tableName;
        parent::__construct('criteres');
        $this->setAttribute('method', 'post');

        if ($this->tableName == 'generic') {
            $this->addGenericElements($tableName);
        } else {
            $method = 'form' . ucwords(strtolower($tableName));
            if (method_exists($this, $method)) {
                $this->$method();
            }
        }
        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'title' => 'Rechercher',
                    'id' => 'criteres-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'fam-find button submit'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        if ($this->tableName == 'generic') {
            return $this->genericSpecification();
        } else {
            $method = 'form' . ucwords(strtolower($this->tableName)) . 'Specification';
            if (method_exists($this, $method)) {
                return $this->$method();
            } else {
                return [];
            }
        }
    }

    /**
     * Affecte une classe css à tous les éléments du formulaire
     *
     * @param string $css_class
     */
    public function setCssClass($css_class)
    {
        foreach ($this->getElements() as $element) {
            $element->setAttribute('class', $css_class);
        }
    }

    /**
     * Affecte les values_options à l'élément indiqué
     *
     * @param string $element
     * @param array $values_options
     */
    public function setValueOptions($element, array $values_options)
    {
        $e = $this->get($element);
        $e->setValueOptions($values_options);
        return $this;
    }

    /**
     * Renvoie un tableau contenant les noms des champs du formulaire (sans submit)
     *
     * @return array
     */
    public function getElementNames()
    {
        $array = [];
        foreach ($this->getElements() as $element) {
            if ($element->getName() != 'submit') {
                $array[] = $element->getName();
            }
        }
        return $array;
    }

    private function formCircuits()
    {
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'serviceId',
                'attributes' => [
                    'id' => 'critere-serviceId',
                    'class' => 'sbm-width-55c'
                ],
                'options' => [
                    'label' => 'Service',
                    'label_attributes' => [
                        'class' => ''
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
                    'class' => 'sbm-width-55c'
                ],
                'options' => [
                    'label' => 'Station',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'empty_option' => 'Toutes',
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
                    'type' => 'checkbox',
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sélectionnés',
                    'label_attributes' => [
                        'class' => 'sbm-new-line'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    private function formCircuitsSpecification()
    {
        return [
            'serviceId' => [
                'name' => 'serviceId',
                'required' => false
            ],
            'stationId' => [
                'name' => 'stationId',
                'required' => false
            ],
            'selection' => [
                'name' => 'selection',
                'required' => false
            ]
        ];
    }

    private function formClasses()
    {
        $this->add(
            [
                'name' => 'nom',
                'type' => 'text',
                'attributes' => [
                    'id' => 'critere-nom',
                    'maxlength' => '30',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Nom',
                    'label_attributes' => [
                        'class' => 'sbm-first'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);

        /*
         * $this->add([ 'name' => 'aliasCG', 'type' => 'hidden', 'attributes' => [ 'id' =>
         * 'critere-aliasCG', 'maxlength' => '30', 'class' => 'sbm-width-30c' ], 'options'
         * => [ 'label' => 'Nom CG', 'error_attributes' => [ 'class' => 'sbm-error' ] ]
         * ]);
         */

        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'selection',
                'attributes' => [
                    'type' => 'checkbox',
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sélectionnés',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    private function formCommunes()
    {
        $this->add(
            [
                'name' => 'departement',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-departement',
                    'maxlength' => '2',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Département',
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
                'name' => 'canton',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-canton',
                    'maxlength' => '4',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Canton',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'codePostal',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-codePostal',
                    'maxlength' => '5',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Code postal',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'nom',
                'attributes' => [
                    'type' => 'text',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'membre',
                'attributes' => [
                    'type' => 'checkbox',
                    'useHiddenElement' => true,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Membres',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'desservie',
                'attributes' => [
                    'type' => 'checkbox',
                    'useHiddenElement' => true,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Desservies',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'visible',
                'attributes' => [
                    'type' => 'checkbox',
                    'useHiddenElement' => true,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Visibles',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'inscriptionenligne',
                'attributes' => [
                    'type' => 'checkbox',
                    'useHiddenElement' => true,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Inscription en ligne',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'paiementenligne',
                'attributes' => [
                    'type' => 'checkbox',
                    'useHiddenElement' => true,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Paiement en ligne',
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
                    'type' => 'checkbox',
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sélectionnés',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    private function formCommunesSpecification()
    {
        return [
            'departement' => [
                'name' => 'departement',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'canton' => [
                'name' => 'canton',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'codePostal' => [
                'name' => 'codePostal',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'nom' => [
                'name' => 'nom',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'membre' => [
                'name' => 'membre',
                'required' => false
            ],
            'desservie' => [
                'name' => 'desservie',
                'required' => false
            ],
            'visible' => [
                'name' => 'visible',
                'required' => false
            ],
            'selection' => [
                'name' => 'selection',
                'required' => false
            ]
        ];
    }

    private function formEleves()
    {
        $this->add(
            [
                'name' => 'numero',
                'attributes' => [
                    'type' => 'text',
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
                'name' => 'nomSA',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-nom',
                    'maxlength' => '45',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Nom',
                /*'label_attributes' => [
                    'class' => 'sbm-first'
                ],*/
                'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'prenomSA',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-prenom',
                    'maxlength' => '45',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Préom',
                /*'label_attributes' => [
                 'class' => 'sbm-first'
                ],*/
                'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'responsable',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-responsable',
                    'maxlength' => '45',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Responsable',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'commune',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-commune',
                    'maxlength' => '30',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Commune',
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
                'name' => 'station',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-station',
                    'maxlength' => '30',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Station',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'service',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-service',
                    'maxlength' => '11',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Service',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        /*
         * $this->add([ 'name' => 'tarifMontant', 'attributes' => [ 'type' => 'text', 'id'
         * => 'critere-tarif', 'maxlength' => '11', 'class' => 'sbm-width-10c' ],
         * 'options' => [ 'label' => 'Tarif', 'error_attributes' => [ 'class' =>
         * 'sbm-error' ] ] ]); $this->add([ 'name' => 'prelevement', 'type' =>
         * 'Zend\Form\Element\Checkbox', 'attributes' => [ 'type' => 'checkbox',
         * 'useHiddenElement' => true, 'options' => [ 'checkedValue' => false,
         * 'uncheckedValue' => true ], 'class' => 'sbm-checkbox' ], 'options' => [ 'label'
         * => 'Prélevés', 'error_attributes' => [ 'class' => 'sbm-error' ] ] ]);
         */
        $this->add(
            [
                'name' => 'etablissement',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-etablissement',
                    'maxlength' => '45',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Établissement',
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
                'name' => 'classe',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-classe',
                    'maxlength' => '30',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Classe',
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
                    'type' => 'checkbox',
                    'useHiddenElement' => true,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sélectionnés',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    private function formElevesSpecification()
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
            'commune' => [
                'name' => 'commune',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'station' => [
                'name' => 'station',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'service' => [
                'name' => 'service',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'etablissement' => [
                'name' => 'etablissement',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'classe' => [
                'name' => 'classe',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'selection' => [
                'name' => 'selection',
                'required' => false
            ]
        ];
    }

    private function formEtablissements()
    {
        $this->add(
            [
                'type' => 'text',
                'name' => 'commune',
                'attributes' => [
                    'id' => 'critere-commune',
                    'maxlength' => '45',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Commune',
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
                'name' => 'nom',
                'attributes' => [
                    'type' => 'text',
                    'maxlength' => '45',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Nom',
                    'id' => 'critere-nom',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        /*
         * $this->add([ 'name' => 'aliasCG', 'attributes' => [ 'type' => 'hidden', 'id' =>
         * 'critere-aliasCG', 'maxlength' => '50', 'class' => 'sbm-width-50c' ], 'options'
         * => [ 'label' => 'Nom CG', 'error_attributes' => [ 'class' => 'sbm-error' ] ]
         * ]);
         */
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'desservie',
                'attributes' => [
                    'type' => 'checkbox',
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Desservis',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'visible',
                'attributes' => [
                    'type' => 'checkbox',
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Visibles',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'localisation',
                'attributes' => [
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sans localisation',
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
                    'type' => 'checkbox',
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sélectionnés',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    private function formEtablissementsSpecification()
    {
        return [
            'commune' => [
                'name' => 'commune',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'nom' => [
                'name' => 'nom',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'selection' => [
                'name' => 'selection',
                'required' => false
            ],
            'commune' => [
                'name' => 'commune',
                'required' => false
            ],
            'localisation' => [
                'name' => 'preinscrits',
                'required' => false
            ]
        ];
    }

    private function formLibelles()
    {
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'nature',
                'attributes' => [
                    'id' => 'critere-code-caisse',
                    'class' => 'sbm-width-20c'
                ],
                'options' => [
                    'label' => 'Nature',
                    'label_attributes' => [
                        'class' => 'sbm-first'
                    ],
                    'empty_option' => 'Toutes',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'ouvert',
                'attributes' => [
                    'useHiddenElement' => true,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Ouvert ',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    private function formLibellesSpecification()
    {
        return [
            'nature' => [
                'name' => 'nature',
                'required' => false
            ],
            'ouvert' => [
                'name' => 'ouvert',
                'required' => false
            ]
        ];
    }

    private function formPaiements()
    {
        $this->add(
            [
                'name' => 'responsable',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-responsable',
                    'class' => 'sbm-width-50c',
                    'maxlegth' => '61'
                ],
                'options' => [
                    'label' => 'Responsable',
                    'label_attributes' => [
                        'class' => 'sbm-critere-responsable sbm-first'
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
                    'type' => 'checkbox',
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sélectionnés',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'codeCaisse',
                'attributes' => [
                    'id' => 'critere-code-caisse',
                    'class' => 'sbm-select1'
                ],
                'options' => [
                    'label' => 'Caisse',
                    'label_attributes' => [
                        'class' => 'sbm-new-line'
                    ],
                    'empty_option' => 'Toutes',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'codeModeDePaiement',
                'attributes' => [
                    'id' => 'critere-code-mode-de-paiement',
                    'class' => 'sbm-select1'
                ],
                'options' => [
                    'label' => 'Mode de paiement',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'empty_option' => 'Toutes',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'exercice',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-exercice',
                    'class' => 'sbm-width-5c',
                    'maxlegth' => '4'
                ],
                'options' => [
                    'label' => 'Exercice',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'anneeScolaire',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-annee-scolaire',
                    'class' => 'sbm-width-10c',
                    'maxlegth' => '9'
                ],
                'options' => [
                    'label' => 'Année scolaire',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Date',
                'name' => 'dateValeur',
                'attributes' => [
                    'id' => 'critere-date-valeur',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Date de valeur',
                    'label_attributes' => [
                        'class' => 'sbm-critere-date-valeur sbm-first sbm-new-line'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Date',
                'name' => 'datePaiement',
                'attributes' => [
                    'id' => 'critere-date-paiement',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Date de paiement',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Date',
                'name' => 'dateDepot',
                'attributes' => [
                    'id' => 'critere-date-depot',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Date de dépôt',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'titulaire',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-titulaire',
                    'class' => 'sbm-width-30c',
                    'maxlegth' => '30'
                ],
                'options' => [
                    'label' => 'Titulaire',
                    'label_attributes' => [
                        'class' => 'sbm-critere-titulaire sbm-first sbm-new-line'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'banque',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-banque',
                    'class' => 'sbm-width-30c',
                    'maxlegth' => '30'
                ],
                'options' => [
                    'label' => 'Banque',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'reference',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-reference',
                    'class' => 'sbm-width-30c',
                    'maxlegth' => '30'
                ],
                'options' => [
                    'label' => 'Référence',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    private function formPaiementsSpecification()
    {
        return [
            'responsable' => [
                'name' => 'responsable',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'exercice' => [
                'name' => 'exercice',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'anneeScolaire' => [
                'name' => 'anneeScolaire',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'titulaire' => [
                'name' => 'titulaire',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'banque' => [
                'name' => 'banque',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'reference' => [
                'name' => 'reference',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'selection' => [
                'name' => 'selection',
                'required' => false
            ],
            'codeCaisse' => [
                'name' => 'codeCaisse',
                'required' => false
            ],
            'codeModeDePaiement' => [
                'name' => 'codeModeDePaiement',
                'required' => false
            ],
            'dateDepot' => [
                'name' => 'dateDepot',
                'required' => false
            ],
            'datePaiement' => [
                'name' => 'datePaiement',
                'required' => false
            ],
            'dateValeur' => [
                'name' => 'dateValeur',
                'required' => false
            ]
        ];
    }

    private function formResponsables()
    {
        $this->add(
            [
                'name' => 'nomSA',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-nom',
                    'maxlength' => '30',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Nom',
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
                'name' => 'prenomSA',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-prenom',
                    'maxlength' => '30',
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
                'name' => 'commune',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-commune',
                    'maxlength' => '45',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Commune',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'nbEnfants',
                'attributes' => [
                    'id' => 'critere-nbEnfants',
                    'maxlength' => 2,
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Nb d\'enfants',
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
                'type' => 'text',
                'name' => 'nbInscrits',
                'attributes' => [
                    'id' => 'critere-nbInscrits',
                    'maxlength' => 2,
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Nb d\'inscrits',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'nbPreinscrits',
                'attributes' => [
                    'id' => 'critere-nbPreinscits',
                    'maxlength' => 2,
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Nb de préinscrits',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'demenagement',
                'attributes' => [
                    'useHiddenElement' => true,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Déménagement',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'inscrits',
                'attributes' => [
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Inscrits',
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
                'name' => 'preinscrits',
                'attributes' => [
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Préinscrits',
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
                'name' => 'localisation',
                'attributes' => [
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sans localisation',
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
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sélectionnés',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    private function formResponsablesSpecification()
    {
        return [
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
            'commune' => [
                'name' => 'commune',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'nbEnfants' => [
                'name' => 'nbEnfants',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'nbInscrits' => [
                'name' => 'nbInscrits',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'nbPreinscrits' => [
                'name' => 'nbPreinscrits',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'demenagement' => [
                'name' => 'demenagement',
                'required' => false
            ],
            'inscrits' => [
                'name' => 'inscrits',
                'required' => false
            ],
            'preinscrits' => [
                'name' => 'preinscrits',
                'required' => false
            ],
            'localisation' => [
                'name' => 'preinscrits',
                'required' => false
            ],
            'selection' => [
                'name' => 'selection',
                'required' => false
            ]
        ];
    }

    private function formSecteursscolairesclgpu()
    {
        $this->add(
            [
                'name' => 'etablissementId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'critere-etablissementId',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Établissement',
                    'empty_option' => 'Tous',
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
                    'id' => 'critere-communeId',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Commune',
                    'empty_option' => 'Toutes',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    private function formSecteursscolairesclgpuSpecification()
    {
        return [
            'etablissementId' => [
                'name' => 'etablissementId',
                'required' => false
            ],
            'communeId' => [
                'name' => 'communeId',
                'required' => false
            ]
        ];
    }

    private function formSimulationetablissements()
    {
        $this->add(
            [
                'name' => 'communeetaborigineId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'critere-origineId',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Commune de l\'établissement d\'origine',
                    'label_attributes' => [
                        'class' => 'sbm-first'
                    ],
                    'empty_option' => 'Tous',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'suivantId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'critere-suivantId',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Établissement suivant',
                    'label_attributes' => [
                        'class' => 'sbm-new-line'
                    ],
                    'empty_option' => 'Tous',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    private function formSimulationetablissementsSpecification()
    {
        return [
            'communeetaborigineId' => [
                'name' => 'origineId',
                'required' => false
            ],
            'suivantId' => [
                'name' => 'suivantId',
                'required' => false
            ]
        ];
    }

    private function formServices()
    {
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'serviceId',
                'attributes' => [
                    'id' => 'critere-serviceId',
                    'maxlength' => '11',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Code',
                    'label_attributes' => [
                        'class' => 'sbm-first'
                    ],
                    'empty_option' => 'Tous',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'nom',
                'attributes' => [
                    'type' => 'text',
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
                'type' => 'Zend\Form\Element\Select',
                'name' => 'transporteurId',
                'attributes' => [
                    'id' => 'critere-transporteurId',
                    'maxlength' => '30',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Transporteur',
                    'empty_option' => 'Tous',
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
                    'type' => 'checkbox',
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sélectionnés',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    private function formServicesSpecification()
    {
        return [
            'serviceId' => [
                'name' => 'serviceId',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'nom' => [
                'name' => 'nom',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'selection' => [
                'name' => 'selection',
                'required' => false
            ],
            'transporteurId' => [
                'name' => 'transporteurId',
                'required' => false
            ]
        ];
    }

    private function formStations()
    {
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'communeId',
                'attributes' => [
                    'id' => 'critere-communeId',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Commune',
                    'label_attributes' => [
                        'class' => 'sbm-first'
                    ],
                    'empty_option' => 'Tous',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'nom',
                'attributes' => [
                    'type' => 'text',
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
        /*
         * $this->add([ 'name' => 'aliasCG', 'attributes' => [ 'type' => 'hidden', 'id' =>
         * 'critere-aliasCG', 'maxlength' => '45', 'class' => 'sbm-width-25c' ], 'options'
         * => [ 'label' => 'Nom CG', 'error_attributes' => [ 'class' => 'sbm-error' ] ]
         * ]);
         */

        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'ouverte',
                'attributes' => [
                    'type' => 'checkbox',
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Ouvertes',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'visible',
                'attributes' => [
                    'type' => 'checkbox',
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Visibles',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'localisation',
                'attributes' => [
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sans localisation',
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
                    'type' => 'checkbox',
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sélectionnés',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    private function formStationsSpecification()
    {
        return [
            'nom' => [
                'name' => 'nom',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'ouverte' => [
                'name' => 'ouverte',
                'required' => false
            ],
            'visible' => [
                'name' => 'visible',
                'required' => false
            ],
            'selection' => [
                'name' => 'selection',
                'required' => false
            ],
            'communeId' => [
                'name' => 'communeId',
                'required' => false
            ],
            'localisation' => [
                'name' => 'preinscrits',
                'required' => false
            ]
        ];
    }

    private function formTarifs()
    {
        $this->add(
            [
                'name' => 'montant',
                'type' => 'text',
                'attributes' => [
                    'id' => 'critere-montant',
                    'maxlength' => '11',
                    'class' => 'sbm-width-10c'
                ],
                'options' => [
                    'label' => 'Montant',
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
                'type' => 'Zend\Form\Element\Select',
                'name' => 'rythme',
                'attributes' => [
                    'id' => 'critere-rytme',
                    'class' => 'sbm-select2'
                ],
                'options' => [
                    'label' => 'Rythme de paiement',
                    'empty_option' => 'Tous',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);

        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'grille',
                'attributes' => [
                    'id' => 'critere-grille',
                    'class' => 'sbm-select2'
                ],
                'options' => [
                    'label' => 'Grille tarifaire',
                    'empty_option' => 'Toutes',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);

        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'mode',
                'attributes' => [
                    'id' => 'critere-mode',
                    'class' => 'sbm-select2'
                ],
                'options' => [
                    'label' => 'Mode de paiement',
                    'empty_option' => 'Tous',
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
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sélectionnés',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    private function formTarifsSpecification()
    {
        return [
            'montant' => [
                'name' => 'montant',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'selection' => [
                'name' => 'selection',
                'required' => false
            ],
            'rythme' => [
                'name' => 'rythme',
                'required' => false
            ],
            'grille' => [
                'name' => 'grille',
                'required' => false
            ],
            'mode' => [
                'name' => 'mode',
                'required' => false
            ]
        ];
    }

    private function formTransporteurs()
    {
        $this->add(
            [
                'name' => 'commune',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-commune',
                    'maxlength' => '45',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Commune',
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
                'name' => 'nom',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-nom',
                    'maxlength' => '30',
                    'class' => 'sbm-width-30c'
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'selection',
                'attributes' => [
                    'type' => 'checkbox',
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sélectionnés',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    private function formUsers()
    {
        $this->add(
            [
                'name' => 'nom',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-nom',
                    'maxlength' => '30',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Nom',
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
                'name' => 'email',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'critere-nom',
                    'maxlength' => '80',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Email',
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
                    'id' => 'critere-categorieId',
                    'class' => 'sbm-width-30c'
                ],
                'options' => [
                    'label' => 'Catégorie',
                    'empty_option' => 'Toutes',
                    'value_options' => [
                        '1' => 'Parent',
                        '2' => 'Transporteur',
                        '3' => 'Établissement scolaire',
                        '250' => 'Secrétariat',
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
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => 1,
                        'uncheckedValue' => 0
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Mot de passe inactif',
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
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'confirme',
                'attributes' => [
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => 0,
                        'uncheckedValue' => 1
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Pas confirmés',
                    'label_attributes' => [
                        'class' => ''
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
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => 0,
                        'uncheckedValue' => 1
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Inactifs',
                    'label_attributes' => [
                        'class' => ''
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
                    'useHiddenElement' => false,
                    'options' => [
                        'checkedValue' => false,
                        'uncheckedValue' => true
                    ],
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Sélectionnés',
                    'label_attributes' => [
                        'class' => ''
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
    }

    private function formUsersSpecification()
    {
        return [
            'categorieId' => [
                'name' => 'categorieId',
                'required' => false
            ],
            'tokenalive' => [
                'name' => 'tokenalive',
                'required' => false
            ],
            'confirme' => [
                'name' => 'confirme',
                'required' => false
            ],
            'active' => [
                'name' => 'active',
                'required' => false
            ],
            'selection' => [
                'name' => 'selection',
                'required' => false
            ]
        ];
    }

    private function addGenericElements($elements)
    {
        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    private function genericSpecification()
    {
        $array = [];
        foreach ($this->getElementNames() as $elementName) {
            $array[$elementName] = [
                'name' => $elementName,
                'required' => false
            ];
            $element = $this->get($elementName);
            if ($element->getAttribute('type') == 'text') {
                $array[$elementName]['filters'][] = [
                    'name' => 'StringTrim'
                ];
            }
        }
        return $array;
    }
}