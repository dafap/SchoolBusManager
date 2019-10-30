<?php
/**
 * Formulaire de saisie/modification des enregistrements de la table systeme `doctables`
 *
 * Un document structuré en tableaux contient pour chaque tableau 3 sections ('thead', 'tbody' et 'tfoot')
 * Chaque section est identifiée par doctableId, mais aussi par (documentId, ordinal_table, section) qui est unique.
 *
 * Description de la structure de la table
 *
 * 'doctableId' : identifiant unique auto increment,
 * 'documentId' : foreign key sur la table documents, mise à jour et suppression en cascade,
 * 'ordinal_table' : rang du tableau dans le document. S'il n'y a qu'un tableau, on mettra 1
 * 'section' : prend les valeurs thead, tbody ou tfoot, chaque section étant unique pour un tableau
 * 'description' : texte libre pour identifier la section dans la liste des sections,
 * 'visible' : mettre 1 si la section est visible ou 0 si elle n'est pas présente dans le pdf,
 * 'width' : largeur du tableau qui prend la valeur auto ou un nombre de 1 à 100 (en % de la largeur de la zone d'écriture)
 * 'row_height' : hauteur de ligne en unité de mesure configurée dans config/autoload/tcpdf-confif.global.php (en mm pour SBM config de base),
 * 'cell_border' : prend les valeurs 0 (pas de bordure), 1 (encadrement), L (bordure à gauche), R (bordure à droite), T (bordue en haut),
 *                                   B (bordure en bas) ou une combinaison de ces valeurs.
 *                 Restriction: TCPDF permet de configurer en même temps l'épaisseur, la couleur... de la ligne. Ce n'est pas implémenté dans ce module.
 * 'cell_align'  : alignement horizontal du texte dans la cellule (L : à gauche, C : centré, R : à droite, J : justifié)
 * 'cell_link'   : URL ou identifiant retourné par addLink() et appliqué à l'ensemble de la section,
 * 'cell_stretch' : de 0 à 4 (voir ci-dessosu 'value_options' dans le code définissant de l'élément de même nom)
 * 'cell_ignore_min_height' : la hauteur indiquée dans row_height peut être ignorée si la ligne est vide
 * 'cell_calign' : alignement de la cellule par rapport à l'ordonnée Y courante qui définit la ligne de base
 *                (T : cellule au dessus, C : cellule centrée, B : cellule en dessous,
 *                 A : ligne de texte au dessus de la ligne de base, L : centrée sur la ligne de base, D : en dessous de la ligne de base)
 * 'cell_valign' : alignement vertical du texte à l'intérieur de la cellule (M :au milieu, T : en haut, B : en bas)
 * 'draw_color'  : couleur des traits,
 * 'line_width'  : épaisseur des traits dans l'unité de mesure configurée dans config/autoload/tcpdf-confif.global.php (en mm pour SBM config de base),
 * 'fill_color'  : couleur de remplissage,
 * 'text_color'  : couleur du texte,
 * 'font_style' => 'char(2) NOT NULL DEFAULT ""' // '', B, I, U, D, O ou combinaison de 2 d'entre elles

 *
 * @project sbm
 * @package SbmPdf\Form
 * @filesource DocTable.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 oct. 2018
 * @version 2019-2.5.4
 */
namespace SbmPdf\Form;

use SbmCommun\Model\Strategy\Color;
use SbmPdf\Model\Strategy\CellBorder;
use SbmPdf\Model\Strategy\FontStyle;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class DocTable extends Form implements InputFilterProviderInterface
{

    public function __construct($param = 'documentpdf')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->add([
            'type' => 'hidden',
            'name' => 'doctableId'
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'documentId'
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
                'name' => 'ordinal_table',
                'attributes' => [
                    'value' => '1',
                    'id' => 'doctable-ordinal_table'
                ],
                'options' => [
                    'label' => 'Numéro du tableau dans le document',
                    'label_attributes' => [],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                // prend les valeurs thead, tbody ou tfoot
                'type' => 'Zend\Form\Element\Select',
                'name' => 'section',
                'attributes' => [
                    'id' => 'doctable-section'
                ],
                'options' => [
                    'label' => 'Section',
                    'label_attributes' => [],
                    'value_options' => [
                        'thead' => 'Ligne d\'en-tête du tableau',
                        'tbody' => 'Corps du tableau',
                        'tfoot' => 'Ligne de pied du tableau'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'description',
                'attributes' => [
                    'id' => 'doctable-description'
                ],
                'options' => [
                    'label' => 'Description',
                    'label_attributes' => [],
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
                    'id' => 'doctable-visible',
                    'value' => 1,
                    'cheched' => 'checked'
                ],
                'options' => [
                    'label' => 'Section visible',
                    'label_attributes' => [],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                // prend la valeur auto ou un nombre de 1 à 100 (% de la largeur de la
                // zone
                // d'écriture) - null par défaut
                'type' => 'text',
                'name' => 'width',
                'attributes' => [
                    'id' => 'doctable-width'
                ],
                'options' => [
                    'label' => 'Largeur de la section',
                    'label_attributes' => [],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                // nombre
                'type' => 'text',
                'name' => 'row_height',
                'attributes' => [
                    'id' => 'doctable-row_height'
                ],
                'options' => [
                    'label' => 'Hauteur de la section',
                    'label_attributes' => [],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                // 0, 1, L, R, T, B
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'name' => 'cell_border',
                'attributes' => [
                    'id' => 'doctable-cell_border'
                ],
                'options' => [
                    'label' => 'Bordure des cellules',
                    'label_attributes' => [],
                    'value_options' => [
                        '-1' => 'Sans bordure',
                        '1' => 'Bordure autour',
                        'L' => 'Bordure à gauche',
                        'R' => 'Bordure à droite',
                        'T' => 'Bordure au dessus',
                        'B' => 'Bordure au dessous'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                // L, C, R, J
                'type' => 'Zend\Form\Element\Select',
                'name' => 'cell_align',
                'attributes' => [
                    'id' => 'doctable-cell_align'
                ],
                'options' => [
                    'label' => 'Alignement horizontal du texte dans les cellules',
                    'label_attributes' => [],
                    'value_options' => [
                        's' => 'Standard',
                        'L' => 'Aligné à gauche',
                        'C' => 'Centré',
                        'R' => 'Aligné à droite',
                        'J' => 'Justifié'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Url',
                'name' => 'cell_link',
                'attributes' => [
                    'id' => 'doctable-cell_link'
                ],
                'options' => [
                    'label' => 'Lien sur les cellules',
                    'label_attributes' => [],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                // de 0 à 4
                'type' => 'Zend\Form\Element\Select',
                'name' => 'cell_stretch',
                'attributes' => [
                    'id' => 'doctable-cell_stretch'
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
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'cell_ignore_min_height',
                'attributes' => [
                    'id' => 'doctable-cell_ignore_min_height'
                ],
                'options' => [
                    'label' => 'Ignore la hauteur minimale',
                    'label_attributes' => [],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                // T, C, B, A, L, D
                'type' => 'Zend\Form\Element\Select',
                'name' => 'cell_calign',
                'attributes' => [
                    'id' => 'doctable-cell_calign'
                ],
                'options' => [
                    'label' => 'Alignement de la cellule par rapport à la ligne de base',
                    'label_attributes' => [],
                    'value_options' => [
                        'T' => 'Cellule au dessus de la ligne de base',
                        'C' => 'Cellule centrée sur la ligne de base',
                        'B' => 'Cellule en dessous de la ligne de base',
                        'A' => 'Texte au dessus de la ligne de base',
                        'L' => 'Texte centré sur la ligne de base',
                        'D' => 'Texte en dessous de la ligne de base'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                // M, T, B
                'type' => 'Zend\Form\Element\Select',
                'name' => 'cell_valign',
                'attributes' => [
                    'id' => 'doctable-cell_valign'
                ],
                'options' => [
                    'label' => 'Alignement vertical du texte dans les cellules',
                    'label_attributes' => [],
                    'value_options' => [
                        'T' => 'Haut',
                        'M' => 'Milieu',
                        'B' => 'Bas'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Color',
                'name' => 'draw_color',
                'attributes' => [
                    'id' => 'doctable-draw_color'
                ],
                'options' => [
                    'label' => 'Couleur des traits',
                    'label_attributes' => [],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'line_width',
                'attributes' => [
                    'id' => 'doctable-line_width'
                ],
                'options' => [
                    'label' => 'Epaisseur des traits',
                    'label_attributes' => [],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Color',
                'name' => 'fill_color',
                'attributes' => [
                    'id' => 'doctable-fill_color'
                ],
                'options' => [
                    'label' => 'Couleur de remplissage',
                    'label_attributes' => [],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Color',
                'name' => 'text_color',
                'attributes' => [
                    'id' => 'doctable-text_color'
                ],
                'options' => [
                    'label' => 'Couleur du texte',
                    'label_attributes' => [],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                // '', B, I, U, D, O ou combinaison de 2 d'entre elles
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'name' => 'font_style',
                'attributes' => [
                    'id' => 'doctable-font_style'
                ],
                'options' => [
                    'label' => 'Style de la police',
                    'label_attributes' => [],
                    'value_options' => [
                        '-1' => 'Aucun',
                        'B' => 'Gras',
                        'I' => 'Italique',
                        'U' => 'Souligné',
                        'D' => 'Barré',
                        'O' => 'Trait suscrit'
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
                    'id' => 'documentpdf-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'documentpdf-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
        $this->add(
            [
                'name' => 'colonnes',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Liste des colonnes',
                    'id' => 'documentpdf-colonnes',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'ordinal_table' => [
                'name' => 'ordinal_table',
                'required' => true
            ],
            'description' => [
                'name' => 'description',
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
                ]
            ],
            'row_height' => [
                'name' => 'row_height',
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
                ]
            ],
            'cell_link' => [
                'name' => 'cell_link',
                'required' => false
            ],
            'line_width' => [
                'name' => 'line_width',
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
                ]
            ],
            'font_style' => [
                'name' => 'font_style',
                'required' => false
            ]
        ];
    }

    public function setData($data)
    {
        $strategieColor = new Color();
        $strategieCellBorder = new CellBorder();
        $strategieFontStyle = new FontStyle();
        foreach ($data as $key => &$value) {
            if (substr($key, - 6) == '_color') {
                $value = $strategieColor->hydrate($strategieColor->extract($value));
            } elseif ($key == 'cell_border') {
                $value = $strategieCellBorder->hydrate($value);
            } elseif (($key == 'font_style')) {
                $value = $strategieFontStyle->hydrate($value);
            }
        }
        parent::setData($data);
    }

    public function getData($flag = 17)
    {
        $data = parent::getData($flag);
        $arrayData = $data->getArrayCopy();
        $strategieCellBorder = new CellBorder();
        $strategieFontStyle = new FontStyle();
        foreach ($arrayData as $key => &$value) {
            if ($key == 'cell_border') {
                $value = $strategieCellBorder->extract($value);
            } elseif (($key == 'font_style')) {
                $value = $strategieFontStyle->extract($value);
            }
        }
        return $data->exchangeArray($arrayData);
    }
}