<?php
/**
 * Partie du formulaire d'inscription d'un enfant concernant le second responsable 
 * en cas de garde alternée.
 *
 * Cette classe abstraite est utilisée en tant que collection et sera dérivée en précisant
 * la propritété complet dans le constructeur.
 * Afin qu'il n'y ait pas de conflit, tous les nom d'éléments commmencent par r2.
 * Les methodes setData et getData sont adaptées en conséquence pour que ça fonctionne 
 * aussi bien si les datas proviennent de la table (pas de r2 en préfixe du nom des colonnes) 
 * ou du post (r2 en préfixe).
 * 
 * @project sbm
 * @package SbmParent/Form
 * @filesource AbstractResponsable2.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmParent\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\FormInterface;

abstract class AbstractResponsable2 extends AbstractSbmForm implements 
    InputFilterProviderInterface
{

    protected $complet;

    public function __construct()
    {
        parent::__construct('responsable2');
        $this->add(
            [
                'type' => 'hidden',
                'name' => 'r2responsable2Id'
            ]);
        $this->add(
            [
                'type' => 'hidden',
                'name' => 'r2userId'
            ]);
        if ($this->complet) {
            $this->add(
                [
                    'type' => 'Zend\Form\Element\Select',
                    'name' => 'r2titre',
                    'attributes' => [
                        'id' => 'titre',
                        'class' => 'sbm-width-15c',
                        'autofocus' => 'autofocus'
                    ],
                    'options' => [
                        'label' => 'Identité du responsable',
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
                    'type' => 'SbmCommun\Form\Element\NomPropre',
                    'name' => 'r2nom',
                    'attributes' => [
                        'id' => 'nom',
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
                    'type' => 'SbmCommun\Form\Element\Prenom',
                    'name' => 'r2prenom',
                    'attributes' => [
                        'id' => 'prenom',
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
                    'name' => 'r2adresseL1',
                    'type' => 'SbmCommun\Form\Element\Adresse',
                    'attributes' => [
                        'id' => 'adresseL1',
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
                    'name' => 'r2adresseL2',
                    'type' => 'SbmCommun\Form\Element\Adresse',
                    'attributes' => [
                        'id' => 'adresseL2',
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
                    'name' => 'r2codePostal',
                    'type' => 'SbmCommun\Form\Element\CodePostal',
                    'attributes' => [
                        'id' => 'codePostal',
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
                    'name' => 'r2communeId',
                    'type' => 'Zend\Form\Element\Select',
                    'attributes' => [
                        'id' => 'communeId',
                        'class' => 'sbm-width-45c'
                    ],
                    'options' => [
                        'label' => 'Commune',
                        'label_attributes' => [
                            'class' => 'sbm-label'
                        ],
                        'empty_option' => 'Choisissez une commune',
                        'disable_inarray_validator' => true,
                        'allow_empty' => false,
                        'error_attributes' => [
                            'class' => 'sbm-error'
                        ]
                    ]
                ]);
            $this->add(
                [
                    'name' => 'r2telephoneF',
                    'type' => 'SbmCommun\Form\Element\Telephone',
                    'attributes' => [
                        'id' => 'telephoneF',
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
                    'name' => 'r2email',
                    'type' => 'Zend\Form\Element\Email',
                    'attributes' => [
                        'id' => 'email',
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
        }
    }

    public function getInputFilterSpecification()
    {
        if ($this->complet) {
            return [
                'r2telephoneF' => [
                    'name' => 'r2telephoneF',
                    'required' => false
                ],
                'r2email' => [
                    'name' => 'r2email',
                    'required' => false
                ]
            ];
        } else {
            return [
                'r2titre' => [
                    'name' => 'r2titre',
                    'required' => false
                ],
                'r2nom' => [
                    'name' => 'r2nom',
                    'required' => false
                ],
                'r2prenom' => [
                    'name' => 'r2prenom',
                    'required' => false
                ],
                'r2adresseL1' => [
                    'name' => 'r2adresseL1',
                    'required' => false
                ],
                'r2adresseL2' => [
                    'name' => 'r2adresseL2',
                    'required' => false
                ],
                'r2codePostal' => [
                    'name' => 'r2codePostal',
                    'required' => false
                ],
                'r2communeId' => [
                    'name' => 'r2communeId',
                    'required' => false
                ],
                'r2telephoneF' => [
                    'name' => 'r2telephoneF',
                    'required' => false
                ],
                'r2email' => [
                    'name' => 'r2email',
                    'required' => false
                ]
            ];
        }
    }

    /**
     * Ajoute le préfixe r2 aux clés qui ne l'ont pas
     *
     * (non-PHPdoc)
     *
     * @see \Zend\Form\Form::setData()
     */
    public function setData($data)
    {
        $d = array_combine(
            preg_replace('/^/', 'r2', preg_replace('/^(r2)/', '', array_keys($data))), 
            array_values($data));
        parent::setData($d);
        return $this;
    }

    /**
     * Supprime le préfice r2 aux clés qui l'ont et renvoie un responsableId au lieu d'un responsable2Id
     *
     * (non-PHPdoc)
     *
     * @see \Zend\Form\Form::getData()
     */
    public function getData($flag = FormInterface::VALUES_NORMALIZED)
    {
        $a = parent::getData($flag);
        return array_combine(
            preg_replace('/(2Id)$/', 'Id', preg_replace('/^(r2)/', '', array_keys($a))), 
            array_values($a));
    }
}