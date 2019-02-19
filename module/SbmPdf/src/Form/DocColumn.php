<?php
/**
 * Formulaire de saisie/modification d'une colonne d'un tableau d'un document pdf
 *
 * Description de la structure de la table
 * 
 * @project sbm
 * @package SbmPdf\Form
 * @filesource DocColumn.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmPdf\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class DocColumn extends Form implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('documentpdf');
        $this->setAttribute('method', 'post');
        $this->add([
            'type' => 'hidden',
            'name' => 'doccolumnId'
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'documentId'
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'name'
        ]);
        $this->add(
            [
                'type' => 'hidden',
                'name' => 'ordinal_table',
                'attributes' => [
                    'value' => '1'
                ]
            ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'recordSource'
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
                'type' => 'text',
                'name' => 'ordinal_position',
                'attributes' => [
                    'id' => 'colonne-ordinal_position'
                ],
                'options' => [
                    'label' => 'Rang de la colonne dans le tableau',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'thead',
                'attributes' => [
                    'id' => 'colonne-thead'
                ],
                'options' => [
                    'label' => 'Libellé sur la ligne d\'en-tête',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'thead_align',
                'attributes' => [
                    'id' => 'colonne-thead_align'
                ],
                'options' => [
                    'label' => 'Alignement horizontal du texte dans la cellule',
                    'label_attributes' => [],
                    'value_options' => [
                        'L' => 'Aligné à gauche',
                        'C' => 'Centré',
                        'R' => 'Aligné à droite',
                        'J' => 'Justifié'
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'thead_stretch',
                'attributes' => [
                    'id' => 'colonne-thead_stretch'
                ],
                'options' => [
                    'label' => 'Etalement',
                    'label_attributes' => [],
                    'value_options' => [
                        '0' => 'Sans étalement',
                        '1' => 'Etalement par mise à l\'échelle si le texte est plus large que la cellule',
                        '2' => 'Etalement par mise à l\'échelle à la largeur de la cellule',
                        '3' => 'Etalement par réglage de l\'espacement si le texte est plus large que la cellule',
                        '4' => 'Etalement par réglage de l\'espacement à la largeur de la cellule'
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'thead_precision',
                'attributes' => [
                    'id' => 'colonne-thead_precision'
                ],
                'options' => [
                    'label' => 'Précision',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'thead_completion',
                'attributes' => [
                    'id' => 'colonne-thead_completion'
                ],
                'options' => [
                    'label' => 'Complétion à gauche (nombre total de caractères de la cellule)',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'tbody',
                'attributes' => [
                    'id' => 'colonne-tbody'
                ],
                'options' => [
                    'label' => 'Donnée de la colonne',
                    'label_attributes' => [],
                    'empty_option' => 'Choisissez une colonne',
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'tbody_align',
                'attributes' => [
                    'id' => 'colonne-tbody_align'
                ],
                'options' => [
                    'label' => 'Alignement horizontal du texte dans la cellule',
                    'label_attributes' => [],
                    'value_options' => [
                        'L' => 'Aligné à gauche',
                        'C' => 'Centré',
                        'R' => 'Aligné à droite',
                        'J' => 'Justifié'
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'tbody_stretch',
                'attributes' => [
                    'id' => 'colonne-tbody_stretch'
                ],
                'options' => [
                    'label' => 'Etalement',
                    'label_attributes' => [],
                    'value_options' => [
                        '0' => 'Sans étalement',
                        '1' => 'Etalement par mise à l\'échelle si le texte est plus large que la cellule',
                        '2' => 'Etalement par mise à l\'échelle à la largeur de la cellule',
                        '3' => 'Etalement par réglage de l\'espacement si le texte est plus large que la cellule',
                        '4' => 'Etalement par réglage de l\'espacement à la largeur de la cellule'
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'tbody_precision',
                'attributes' => [
                    'id' => 'colonne-tbody_precision'
                ],
                'options' => [
                    'label' => 'Précision',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'tbody_completion',
                'attributes' => [
                    'id' => 'colonne-tbody_completion'
                ],
                'options' => [
                    'label' => 'Complétion à gauche (nombre total de caractères de la colonne)',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'tfoot',
                'attributes' => [
                    'id' => 'colonne-tfoot'
                ],
                'options' => [
                    'label' => 'Contenu de la ligne du bas',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'tfoot_align',
                'attributes' => [
                    'id' => 'colonne-tfoot_align'
                ],
                'options' => [
                    'label' => 'Alignement horizontal du texte dans la cellule',
                    'label_attributes' => [],
                    'value_options' => [
                        'L' => 'Aligné à gauche',
                        'C' => 'Centré',
                        'R' => 'Aligné à droite',
                        'J' => 'Justifié'
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'tfoot_stretch',
                'attributes' => [
                    'id' => 'colonne-tfoot_stretch'
                ],
                'options' => [
                    'label' => 'Etalement',
                    'label_attributes' => [],
                    'value_options' => [
                        '0' => 'Sans étalement',
                        '1' => 'Etalement par mise à l\'échelle si le texte est plus large que la cellule',
                        '2' => 'Etalement par mise à l\'échelle à la largeur de la cellule',
                        '3' => 'Etalement par réglage de l\'espacement si le texte est plus large que la cellule',
                        '4' => 'Etalement par réglage de l\'espacement à la largeur de la cellule'
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'tfoot_precision',
                'attributes' => [
                    'id' => 'colonne-tfoot_precision'
                ],
                'options' => [
                    'label' => 'Précision ',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'tfoot_completion',
                'attributes' => [
                    'id' => 'colonne-tfoot_completion'
                ],
                'options' => [
                    'label' => 'Complétion à gauche (nombre total de caractères de la cellule)',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'filter',
                'attributes' => [
                    'id' => 'colonne-filter',
                    'class' => 'sbm-width-55c'
                ],
                'options' => [
                    'label' => 'Valeurs de remplacement',
                    'label_attributes' => [
                        'class' => 'sbm-label-top'
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'width',
                'attributes' => [
                    'id' => 'colonne-width'
                ],
                'options' => [
                    'label' => 'Largeur',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'truncate',
                'attributes' => [
                    'id' => 'colonne-truncate'
                ],
                'options' => [
                    'label' => 'Colonne tronquée',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'nl',
                'attributes' => [
                    'id' => 'colonne-nl'
                ],
                'options' => [
                    'label' => 'Saut de page au changement de valeur',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'documentpdf-cancel',
                    'autofocus' => 'autofocus',
                    'class' => 'button default cancel'
                ]
            ]);
        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Enregistrer',
                    'id' => 'documentpdf-submit',
                    'class' => 'button default submit'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'ordinal_position' => [
                'name' => 'ordinal_position',
                'required' => true
            ],
            'filter' => [
                'name' => 'filter',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'width' => [
                'name' => 'width',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ],
                'thead' => [
                    'name' => 'thead',
                    'required' => false,
                    'filters' => [
                        [
                            'name' => 'StripTags'
                        ],
                        [
                            'name' => 'StringTrim'
                        ]
                    ]
                ],
                'thead_precision' => [
                    'name' => 'thead_precision',
                    'required' => false,
                    'filters' => [
                        [
                            'name' => 'StripTags'
                        ],
                        [
                            'name' => 'StringTrim'
                        ]
                    ]
                ],
                'thead_completion' => [
                    'name' => 'thead_completion',
                    'required' => false,
                    'filters' => [
                        [
                            'name' => 'StripTags'
                        ],
                        [
                            'name' => 'StringTrim'
                        ]
                    ]
                ],
                'tbody_precision' => [
                    'name' => 'tbody_precision',
                    'required' => false,
                    'filters' => [
                        [
                            'name' => 'StripTags'
                        ],
                        [
                            'name' => 'StringTrim'
                        ]
                    ]
                ],
                'tbody_completion' => [
                    'name' => 'tbody_completion',
                    'required' => false,
                    'filters' => [
                        [
                            'name' => 'StripTags'
                        ],
                        [
                            'name' => 'StringTrim'
                        ]
                    ]
                ],
                'tfoot' => [
                    'name' => 'tfoot',
                    'required' => false,
                    'filters' => [
                        [
                            'name' => 'StripTags'
                        ],
                        [
                            'name' => 'StringTrim'
                        ]
                    ]
                ],
                'tfoot_precision' => [
                    'name' => 'tfoot_precision',
                    'required' => false,
                    'filters' => [
                        [
                            'name' => 'StripTags'
                        ],
                        [
                            'name' => 'StringTrim'
                        ]
                    ]
                ],
                'tfoot_completion' => [
                    'name' => 'tfoot_completion',
                    'required' => false,
                    'filters' => [
                        [
                            'name' => 'StripTags'
                        ],
                        [
                            'name' => 'StringTrim'
                        ]
                    ]
                ]
            ]
        ];
    }

    public function setMaxLength(array $array)
    {
        foreach ($array as $elementName => $maxLength) {
            try {
                $e = $this->get($elementName);
                $type = $e->getAttribute('type');
                if (! is_null($type) && $type == 'text') {
                    $e->setAttribute('maxlength', $maxLength);
                }
            } catch (Exception $e) {}
        }
    }

    public function setValueOptions($element, array $values_options)
    {
        $e = $this->get($element);
        $e->setValueOptions($values_options);
        return $this;
    }
}