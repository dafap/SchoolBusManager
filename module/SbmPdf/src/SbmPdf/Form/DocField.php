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
 * @date 26 juil. 2015
 * @version 2015-1
 */
namespace SbmPdf\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class DocField extends Form implements InputFilterProviderInterface
{

    private $sm;

    public function __construct($sm, $param = 'documentpdf')
    {
        $this->sm = $sm;
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'type' => 'hidden',
            'name' => 'docfieldId'
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
                'id' => 'field-ordinal_position'
            ),
            'options' => array(
                'label' => 'Rang du champ dans le document',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'fieldname',
            'attributes' => array(
                'id' => 'field-fieldname'
            ),
            'options' => array(
                'label' => 'Donnée à mettre dans ce champ',
                'label_attributes' => array(),
                'empty_option' => 'Choisissez un champ',
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'fieldname_width',
            'attributes' => array(
                'id' => 'field-fieldname_width'
            ),
            'options' => array(
                'label' => 'Largeur (mettre 0 pour ne pas imposer la largeur)',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'fieldname_align',
            'attributes' => array(
                'id' => 'field-fieldname_align'
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
            'name' => 'fieldname_stretch',
            'attributes' => array(
                'id' => 'field-fieldname_stretch'
            ),
            'options' => array(
                'label' => 'Etalement (si la largeur est fixée)',
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
            'name' => 'fieldname_precision',
            'attributes' => array(
                'id' => 'field-fieldname_precision'
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
            'name' => 'fieldname_completion',
            'attributes' => array(
                'id' => 'field-fieldname_completion'
            ),
            'options' => array(
                'label' => 'Complétion à gauche (nombre total de caractères pour ce champ)',
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
                'id' => 'field-filter',
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
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'is_date',
            'attributes' => array(
                'id' => 'field-is_date'
            ),
            'options' => array(
                'label' => 'Est-ce une date ?',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'format',
            'attributes' => array(
                'id' => 'field-format',
                'class' => 'sbm-width-55c'
            ),
            'options' => array(
                'label' => 'Description du format',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'label',
            'attributes' => array(
                'id' => 'field-label',
                'class' => 'sbm-width-55c'
            ),
            'options' => array(
                'label' => 'Texte précédent la donnée',
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
            'name' => 'label_space',
            'attributes' => array(
                'id' => 'field-label_space'
            ),
            'options' => array(
                'label' => 'Espacement du texte par rapport à la donnée',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'label_width',
            'attributes' => array(
                'id' => 'field-label_width'
            ),
            'options' => array(
                'label' => 'Largeur ou marge gauche (mettre 0 pour ne pas imposer de largeur)',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'label_align',
            'attributes' => array(
                'id' => 'field-label_align'
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
            'name' => 'label_stretch',
            'attributes' => array(
                'id' => 'field-label_stretch'
            ),
            'options' => array(
                'label' => 'Etalement (si la largeur est fixée)',
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
            'type' => 'Zend\Form\Element\Select',
            'name' => 'style',
            'attributes' => array(
                'id' => 'field-style'
            ),
            'options' => array(
                'label' => 'Style',
                'label_attributes' => array(),
                'empty_option' => 'Choisissez',
                'value_options' => array(
                    'main' => 'Police principale',
                    'data' => 'Police des données',
                    'titre1' => 'Titre 1',
                    'titre2' => 'Titre 2',
                    'titre3' => 'Titre 3',
                    'titre4' => 'Titre 4'
                ),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'height',
            'attributes' => array(
                'id' => 'field-height'
            ),
            'options' => array(
                'label' => 'Hauteur des cellules (label et donnée)',
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
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\Digits'
                    )
                )
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
            'fieldname_width' => array(
                'name' => 'fieldname_width',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => array(
                            'separateur' => '.',
                            'car2sep' => ','
                        )
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'SbmCommun\Model\Validator\Decimal'
                    )
                )
            ),
            'fieldname_completion' => array(
                'name' => 'fieldname_completion',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\Digits'
                    )
                )
            ),
            'fieldname_precision' => array(
                'name' => 'fieldname_precision',
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
            'format' => array(
                'name' => 'format',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'height' => array(
                'name' => 'height',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => array(
                            'separateur' => '.',
                            'car2sep' => ','
                        )
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'SbmCommun\Model\Validator\Decimal'
                    )
                )
            ),
            'label' => array(
                'name' => 'label',
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
            'label_width' => array(
                'name' => 'label_width',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => array(
                            'separateur' => '.',
                            'car2sep' => ','
                        )
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'SbmCommun\Model\Validator\Decimal'
                    )
                )
            ),
            'label_space' => array(
                'name' => 'label_space',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => array(
                            'separateur' => '.',
                            'car2sep' => ','
                        )
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'SbmCommun\Model\Validator\Decimal'
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
 