<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource DocColumn.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 juil. 2015
 * @version 2015-1
 */
namespace SbmPdf\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class DocColumn extends Form implements InputFilterProviderInterface
{

    private $sm;

    public function __construct($sm, $param = 'documentpdf')
    {
        $this->sm = $sm;
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'type' => 'hidden',
            'name' => 'doccolumnId'
        ));
        $this->add(array(
            'type' => 'hidden',
            'name' => 'documentId'
        ));
        $this->add(array(
            'type' => 'hidden',
            'name' => 'name'
        ));
        $this->add(array(
            'type' => 'hidden',
            'name' => 'ordinal_table',
            'attributes' => array(
                'value' => '1'
            )
        ));
        $this->add(array(
            'type' => 'hidden',
            'name' => 'recordSource'
        ));
        $this->add(array(
            'name' => 'csrf',
            'type' => 'Zend\Form\Element\Csrf',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 180
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'ordinal_position',
            'attributes' => array(
                'id' => 'colonne-ordinal_position'
            ),
            'options' => array(
                'label' => 'Rang de la colonne dans le tableau',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'thead',
            'attributes' => array(
                'id' => 'colonne-thead'
            ),
            'options' => array(
                'label' => 'Libellé sur la ligne d\'en-tête',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'thead_align',
            'attributes' => array(
                'id' => 'colonne-thead_align'
            ),
            'options' => array(
                'label' => 'Alignement horizontal du texte dans la cellule',
                'label_attributes' => array(),
                'value_options' => array(
                    'L' => 'Aligné à gauche',
                    'C' => 'Centré',
                    'R' => 'Aligné à droite',
                    'J' => 'Justifié'
                ),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'thead_stretch',
            'attributes' => array(
                'id' => 'colonne-thead_stretch'
            ),
            'options' => array(
                'label' => 'Etalement',
                'label_attributes' => array(),
                'value_options' => array(
                    '0' => 'Sans étalement',
                    '1' => 'Etalement par mise à l\'échelle si le texte est plus large que la cellule',
                    '2' => 'Etalement par mise à l\'échelle à la largeur de la cellule',
                    '3' => 'Etalement par réglage de l\'espacement si le texte est plus large que la cellule',
                    '4' => 'Etalement par réglage de l\'espacement à la largeur de la cellule'
                ),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'thead_precision',
            'attributes' => array(
                'id' => 'colonne-thead_precision'
            ),
            'options' => array(
                'label' => 'Précision',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'thead_completion',
            'attributes' => array(
                'id' => 'colonne-thead_completion'
            ),
            'options' => array(
                'label' => 'Complétion à gauche (nombre total de caractères de la cellule)',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'tbody',
            'attributes' => array(
                'id' => 'colonne-tbody'
            ),
            'options' => array(
                'label' => 'Donnée de la colonne',
                'label_attributes' => array(),
                'empty_option' => 'Choisissez une colonne',
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'tbody_align',
            'attributes' => array(
                'id' => 'colonne-tbody_align'
            ),
            'options' => array(
                'label' => 'Alignement horizontal du texte dans la cellule',
                'label_attributes' => array(),
                'value_options' => array(
                    'L' => 'Aligné à gauche',
                    'C' => 'Centré',
                    'R' => 'Aligné à droite',
                    'J' => 'Justifié'
                ),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'tbody_stretch',
            'attributes' => array(
                'id' => 'colonne-tbody_stretch'
            ),
            'options' => array(
                'label' => 'Etalement',
                'label_attributes' => array(),
                'value_options' => array(
                    '0' => 'Sans étalement',
                    '1' => 'Etalement par mise à l\'échelle si le texte est plus large que la cellule',
                    '2' => 'Etalement par mise à l\'échelle à la largeur de la cellule',
                    '3' => 'Etalement par réglage de l\'espacement si le texte est plus large que la cellule',
                    '4' => 'Etalement par réglage de l\'espacement à la largeur de la cellule'
                ),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'tbody_precision',
            'attributes' => array(
                'id' => 'colonne-tbody_precision'
            ),
            'options' => array(
                'label' => 'Précision',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'tbody_completion',
            'attributes' => array(
                'id' => 'colonne-tbody_completion'
            ),
            'options' => array(
                'label' => 'Complétion à gauche (nombre total de caractères de la colonne)',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'tfoot',
            'attributes' => array(
                'id' => 'colonne-tfoot'
            ),
            'options' => array(
                'label' => 'Contenu de la ligne du bas',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'tfoot_align',
            'attributes' => array(
                'id' => 'colonne-tfoot_align'
            ),
            'options' => array(
                'label' => 'Alignement horizontal du texte dans la cellule',
                'label_attributes' => array(),
                'value_options' => array(
                    'L' => 'Aligné à gauche',
                    'C' => 'Centré',
                    'R' => 'Aligné à droite',
                    'J' => 'Justifié'
                ),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'tfoot_stretch',
            'attributes' => array(
                'id' => 'colonne-tfoot_stretch'
            ),
            'options' => array(
                'label' => 'Etalement',
                'label_attributes' => array(),
                'value_options' => array(
                    '0' => 'Sans étalement',
                    '1' => 'Etalement par mise à l\'échelle si le texte est plus large que la cellule',
                    '2' => 'Etalement par mise à l\'échelle à la largeur de la cellule',
                    '3' => 'Etalement par réglage de l\'espacement si le texte est plus large que la cellule',
                    '4' => 'Etalement par réglage de l\'espacement à la largeur de la cellule'
                ),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'tfoot_precision',
            'attributes' => array(
                'id' => 'colonne-tfoot_precision'
            ),
            'options' => array(
                'label' => 'Précision ',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'tfoot_completion',
            'attributes' => array(
                'id' => 'colonne-tfoot_completion'
            ),
            'options' => array(
                'label' => 'Complétion à gauche (nombre total de caractères de la cellule)',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'filter',
            'attributes' => array(
                'id' => 'colonne-filter',
                'class' => 'sbm-width-55c'
            ),
            'options' => array(
                'label' => 'Valeurs de remplacement',
                'label_attributes' => array(
                    'class' => 'sbm-label-top'
                ),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'width',
            'attributes' => array(
                'id' => 'colonne-width'
            ),
            'options' => array(
                'label' => 'Largeur',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'truncate',
            'attributes' => array(
                'id' => 'colonne-truncate'
            ),
            'options' => array(
                'label' => 'Colonne tronquée',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'nl',
            'attributes' => array(
                'id' => 'colonne-nl'
            ),
            'options' => array(
                'label' => 'Saut de page au changement de valeur',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'documentpdf-cancel',
                'autofocus' => 'autofocus',
                'class' => 'button default cancel'
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer',
                'id' => 'documentpdf-submit',
                'class' => 'button default submit'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'ordinal_position' => array(
                'name' => 'ordinal_position',
                'required' => true
            ),
            'filter' => array(
                'name' => 'filter',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'width' => array(
                'name' => 'width',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    ),
                    array(
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => array(
                            'separateur' => '.',
                            'car2sep' => ','
                        )
                    )
                ),
                'thead' => array(
                    'name' => 'thead',
                    'required' => false,
                    'filters' => array(
                        array(
                            'name' => 'StripTags'
                        ),
                        array(
                            'name' => 'StringTrim'
                        )
                    )
                ),
                'thead_precision' => array(
                    'name' => 'thead_precision',
                    'required' => false,
                    'filters' => array(
                        array(
                            'name' => 'StripTags'
                        ),
                        array(
                            'name' => 'StringTrim'
                        )
                    )
                ),
                'thead_completion' => array(
                    'name' => 'thead_completion',
                    'required' => false,
                    'filters' => array(
                        array(
                            'name' => 'StripTags'
                        ),
                        array(
                            'name' => 'StringTrim'
                        )
                    )
                ),
                'tbody_precision' => array(
                    'name' => 'tbody_precision',
                    'required' => false,
                    'filters' => array(
                        array(
                            'name' => 'StripTags'
                        ),
                        array(
                            'name' => 'StringTrim'
                        )
                    )
                ),
                'tbody_completion' => array(
                    'name' => 'tbody_completion',
                    'required' => false,
                    'filters' => array(
                        array(
                            'name' => 'StripTags'
                        ),
                        array(
                            'name' => 'StringTrim'
                        )
                    )
                ),
                'tfoot' => array(
                    'name' => 'tfoot',
                    'required' => false,
                    'filters' => array(
                        array(
                            'name' => 'StripTags'
                        ),
                        array(
                            'name' => 'StringTrim'
                        )
                    )
                ),
                'tfoot_precision' => array(
                    'name' => 'tfoot_precision',
                    'required' => false,
                    'filters' => array(
                        array(
                            'name' => 'StripTags'
                        ),
                        array(
                            'name' => 'StringTrim'
                        )
                    )
                ),
                'tfoot_completion' => array(
                    'name' => 'tfoot_completion',
                    'required' => false,
                    'filters' => array(
                        array(
                            'name' => 'StripTags'
                        ),
                        array(
                            'name' => 'StringTrim'
                        )
                    )
                )
            )
        );
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