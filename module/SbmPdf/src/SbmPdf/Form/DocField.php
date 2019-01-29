<?php
/**
 * Formulaire de saisie/modification d'un champ d'un document pdf (hors présentation tabulaire)
 *
 * Description de la structure de la table
 * 
 * @project sbm
 * @package SbmPdf/Form
 * @filesource DocField.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 jan. 2019
 * @version 2019-2.4.6
 */
namespace SbmPdf\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class DocField extends Form implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('champdocumentpdf');
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'type' => 'hidden',
                'name' => 'docfieldId'
            ]);
        $this->add(
            [
                'type' => 'hidden',
                'name' => 'documentId'
            ]);
        $this->add(
            [
                'type' => 'hidden',
                'name' => 'name'
            ]);
        $this->add(
            [
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
                    'id' => 'field-ordinal_position'
                ],
                'options' => [
                    'label' => 'Rang du champ dans le document',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'fieldname',
                'attributes' => [
                    'id' => 'field-fieldname'
                ],
                'options' => [
                    'label' => 'Donnée à mettre dans ce champ',
                    'label_attributes' => [],
                    'empty_option' => 'Choisissez un champ',
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'fieldname_width',
                'attributes' => [
                    'id' => 'field-fieldname_width'
                ],
                'options' => [
                    'label' => 'Largeur (mettre 0 pour ne pas imposer la largeur)',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'fieldname_align',
                'attributes' => [
                    'id' => 'field-fieldname_align'
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
                'name' => 'fieldname_stretch',
                'attributes' => [
                    'id' => 'field-fieldname_stretch'
                ],
                'options' => [
                    'label' => 'Etalement (si la largeur est fixée)',
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
                'name' => 'fieldname_precision',
                'attributes' => [
                    'id' => 'field-fieldname_precision'
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
                'name' => 'fieldname_completion',
                'attributes' => [
                    'id' => 'field-fieldname_completion'
                ],
                'options' => [
                    'label' => 'Complétion à gauche (nombre total de caractères pour ce champ)',
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
                    'id' => 'field-filter',
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
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'nature',
                'attributes' => [
                    'id' => 'field-nature'
                ],
                'options' => [
                    'label' => 'Nature du champ',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'value_options' => [
                        '2' => [
                            'label' => 'Photo',
                            'value' => 2
                        ],
                        '1' => [
                            'label' => 'Date',
                            'value' => 1
                        ],
                        '0' => [
                            'label' => 'Autre texte',
                            'value' => 0
                        ]
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'format',
                'attributes' => [
                    'id' => 'field-format',
                    'class' => 'sbm-width-55c'
                ],
                'options' => [
                    'label' => 'Description du format',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'label',
                'attributes' => [
                    'id' => 'field-label',
                    'class' => 'sbm-width-55c'
                ],
                'options' => [
                    'label' => 'Texte précédent la donnée',
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
                'name' => 'label_space',
                'attributes' => [
                    'id' => 'field-label_space'
                ],
                'options' => [
                    'label' => 'Espacement du texte par rapport à la donnée',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'label_width',
                'attributes' => [
                    'id' => 'field-label_width'
                ],
                'options' => [
                    'label' => 'Largeur ou marge gauche (mettre 0 pour ne pas imposer de largeur)',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'label_align',
                'attributes' => [
                    'id' => 'field-label_align'
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
                'name' => 'label_stretch',
                'attributes' => [
                    'id' => 'field-label_stretch'
                ],
                'options' => [
                    'label' => 'Etalement (si la largeur est fixée)',
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
                'type' => 'Zend\Form\Element\Select',
                'name' => 'style',
                'attributes' => [
                    'id' => 'field-style'
                ],
                'options' => [
                    'label' => 'Style',
                    'label_attributes' => [],
                    'empty_option' => 'Choisissez',
                    'value_options' => [
                        'main' => 'Police principale',
                        'data' => 'Police des données',
                        'titre1' => 'Titre 1',
                        'titre2' => 'Titre 2',
                        'titre3' => 'Titre 3',
                        'titre4' => 'Titre 4'
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'height',
                'attributes' => [
                    'id' => 'field-height'
                ],
                'options' => [
                    'label' => 'Hauteur des cellules (label et donnée)',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'photo_x',
                'attributes' => [
                    'id' => 'field-x'
                ],
                'options' => [
                    'label' => 'Abscisse du coin supérieur gauche de la photo par rapport à zone d\'écriture',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        
        $this->add(
            [
                'type' => 'text',
                'name' => 'photo_y',
                'attributes' => [
                    'id' => 'field-y'
                ],
                'options' => [
                    'label' => 'Ordonnée du coin supérieur gauche de la photo par rapport à la zone d\'écriture',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'photo_w',
                'attributes' => [
                    'id' => 'field-w'
                ],
                'options' => [
                    'label' => 'Largeur de la photo',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'photo_h',
                'attributes' => [
                    'id' => 'field-h'
                ],
                'options' => [
                    'label' => 'Hauteur de la photo',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'photo_type',
                'attributes' => [
                    'id' => 'field-type'
                ],
                'options' => [
                    'label' => 'Format numérique',
                    'label_attributes' => [],
                    'empty_option' => 'Choisissez dans la liste',
                    'value_options' => [
                        'jpg' => [
                            'label' => 'JPEG',
                            'value' => 'JPEG'
                        ],
                        'png' => [
                            'label' => 'PNG',
                            'value' => 'PNG'
                        ],
                        'gif' => [
                            'label' => 'GIF',
                            'value' => 'GIF'
                        ]
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'photo_align',
                'attributes' => [
                    'id' => 'field-align'
                ],
                'options' => [
                    'label' => 'Alignement vertical',
                    'label_attributes' => [],
                    'empty_option' => 'Choisissez dans la liste',
                    'value_options' => [
                        'top' => [
                            'label' => 'Aligné en haut',
                            'value' => 'T'
                        ],
                        'middle' => [
                            'label' => 'Aligné au milieu',
                            'value' => 'M'
                        ],
                        'bottom' => [
                            'label' => 'Aligné en bas',
                            'value' => 'B'
                        ]
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        
        $this->add(
            [
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'photo_resize',
                'attributes' => [
                    'id' => 'field-resize'
                ],
                'options' => [
                    'label' => 'Redimentionnement',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'value_options' => [
                        '1' => [
                            'label' => 'Proportionnel',
                            'value' => 1
                        ],
                        '0' => [
                            'label' => 'Découpage',
                            'value' => 0
                        ]
                    ],
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
                'required' => true,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\Digits'
                    ]
                ]
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
            'fieldname_width' => [
                'name' => 'fieldname_width',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'SbmCommun\Model\Validator\Decimal'
                    ]
                ]
            ],
            'fieldname_completion' => [
                'name' => 'fieldname_completion',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\Digits'
                    ]
                ]
            ],
            'fieldname_precision' => [
                'name' => 'fieldname_precision',
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
            'format' => [
                'name' => 'format',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'height' => [
                'name' => 'height',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'SbmCommun\Model\Validator\Decimal'
                    ]
                ]
            ],
            'label' => [
                'name' => 'label',
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
            'label_width' => [
                'name' => 'label_width',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'SbmCommun\Model\Validator\Decimal'
                    ]
                ]
            ],
            'label_space' => [
                'name' => 'label_space',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'SbmCommun\Model\Validator\Decimal'
                    ]
                ]
            ],
            'photo_x' => [
                'name' => 'photo_x',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\ToInt'
                    ]
                ]
            ],
            'photo_y' => [
                'name' => 'photo_y',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\ToInt'
                    ]
                ]
            ],
            'photo_w' => [
                'name' => 'photo_w',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\Digits'
                    ]
                ]
            ],
            'photo_h' => [
                'name' => 'photo_h',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\Digits'
                    ]
                ]
            ],
            'photo_type' => [
                'name' => 'photo_type',
                'required' => false
            ],
            'photo_align' => [
                'name' => 'photo_align',
                'required' => false
            ],
            'photo_resize' => [
                'name' => 'photo_resize',
                'required' => false
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
 