<?php
/**
 * Formulaire de saisie/modification d'un format d'étiquettes
 *
 *
 * @project sbm
 * @package SbmPdf/Form
 * @filesource DocLabel.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 oct. 2020
 * @version 2020-2.6.1
 */
namespace SbmPdf\Form;

use SbmCommun\Model\Strategy\Color;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class DocLabel extends Form implements InputFilterProviderInterface
{

    public function __construct($param = 'documentpdf')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        /*$this->add([
            'type' => 'hidden',
            'name' => 'doclabelId'
        ]);*/
        $this->add([
            'type' => 'hidden',
            'name' => 'documentId'
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'sublabel'
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'name'
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
                'name' => 'filigrane',
                'attributes' => [
                    'id' => 'label-filigrane',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Filigrane',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'margin_left',
                'attributes' => [
                    'id' => 'label-margin_left',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Marge de gauche',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'margin_top',
                'attributes' => [
                    'id' => 'label-margin_top',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Marge du haut',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'x_space',
                'attributes' => [
                    'id' => 'label-x_space',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Espacement horizontal entre 2 colonnes d\'étiquettes',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'y_space',
                'attributes' => [
                    'id' => 'label-y_space',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Espacement vertical entre 2 rangées d\'étiquettes',
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
                    'id' => 'label-label_width',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Largeur d\'une étiquette',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'label_height',
                'attributes' => [
                    'id' => 'label-label_height',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Hauteur d\'une étiquette',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'cols_number',
                'attributes' => [
                    'id' => 'label-cols_number',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Nombre de colonnes',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'rows_number',
                'attributes' => [
                    'id' => 'label-rows_number',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Nombre de rangées d\'étiquettes',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'padding_top',
                'attributes' => [
                    'id' => 'label-padding_top',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Marge intérieure en haut',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'padding_right',
                'attributes' => [
                    'id' => 'label-padding_right',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Marge intérieure à droite',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'padding_bottom',
                'attributes' => [
                    'id' => 'label-padding_bottom',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Marge intérieure en bas',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'padding_left',
                'attributes' => [
                    'id' => 'label-padding_left',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Marge intérieure à gauche',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Radio',
                'name' => 'border',
                'attributes' => [
                    'id' => 'label-border',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Bordure',
                    'label_attributes' => [],
                    'value_options' => [
                        '1' => 'Oui',
                        '0' => 'Non'
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'border_dash',
                'attributes' => [
                    'id' => 'label-border_dash',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Trait',
                    'label_attributes' => [],
                    'value_options' => [
                        0 => 'plein',
                        1 => 'tirets',
                        2 => 'pointillés'
                    ],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'border_width',
                'attributes' => [
                    'id' => 'label-border_width',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Epaisseur du trait de bordure',
                    'label_attributes' => [],
                    'error_options' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Color',
                'name' => 'border_color',
                'attributes' => [
                    'id' => 'label-border_color',
                    'class' => ''
                ],
                'options' => [
                    'label' => 'Couleur de la bordure',
                    'label_attributes' => [],
                    'error_options' => [
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
                    'id' => 'documentpdf-submit',
                    'class' => 'button default submit left-95px'
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
    }

    public function getInputFilterSpecification()
    {
        return [
            'margin_left' => [
                'name' => 'margin_left',
                'required' => true,
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
            'margin_top' => [
                'name' => 'margin_top',
                'required' => true,
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
            'x_space' => [
                'name' => 'x_space',
                'required' => true,
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
            'y_space' => [
                'name' => 'y_space',
                'required' => true,
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
            'label_width' => [
                'name' => 'label_width',
                'required' => true,
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
            'label_height' => [
                'name' => 'label_height',
                'required' => true,
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
            'cols_number' => [
                'name' => 'border_width',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\Digits'
                    ]
                ]
            ],
            'rows_number' => [
                'name' => 'border_width',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\Digits'
                    ]
                ]
            ],
            'padding_top' => [
                'name' => 'padding_top',
                'required' => true,
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
            'padding_right' => [
                'name' => 'padding_right',
                'required' => true,
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
            'padding_bottom' => [
                'name' => 'padding_bottom',
                'required' => true,
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
            'padding_left' => [
                'name' => 'padding_left',
                'required' => true,
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
            'border' => [
                'name' => 'border',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'Alnum'
                    ]
                ]
            ],
            'border_dash' => [
                'name' => 'border_dash',
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
            'border_width' => [
                'name' => 'border_width',
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
            ]
        ];
    }

    public function setData($data)
    {
        $strategieColor = new Color();
        foreach ($data as $key => &$value) {
            if (substr($key, - 6) == '_color') {
                $value = $strategieColor->hydrate($strategieColor->extract($value));
            }
        }
        parent::setData($data);
    }

    public function setMaxLength()
    {
    }
}