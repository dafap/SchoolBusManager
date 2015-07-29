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
 * @date 28 juil. 2015
 * @version 2015-1
 */
namespace SbmPdf\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use SbmCommun\Model\Strategy\Color;

class DocLabel extends Form implements InputFilterProviderInterface
{

    public function __construct($param = 'documentpdf')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'type' => 'hidden',
            'name' => 'doclabelId'
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
            'name' => 'margin_left',
            'attributes' => array(
                'id' => 'label-margin_left',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Marge de gauche',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'margin_top',
            'attributes' => array(
                'id' => 'label-margin_top',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Marge du haut',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'x_space',
            'attributes' => array(
                'id' => 'label-x_space',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Espacement horizontal entre 2 colonnes d\'étiquettes',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'y_space',
            'attributes' => array(
                'id' => 'label-y_space',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Espacement vertical entre 2 rangées d\'étiquettes',
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
                'id' => 'label-label_width',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Largeur d\'une étiquette',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'label_height',
            'attributes' => array(
                'id' => 'label-label_height',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Hauteur d\'une étiquette',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'cols_number',
            'attributes' => array(
                'id' => 'label-cols_number',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Nombre de colonnes',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'rows_number',
            'attributes' => array(
                'id' => 'label-rows_number',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Nombre de rangées d\'étiquettes',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'padding_top',
            'attributes' => array(
                'id' => 'label-padding_top',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Marge intérieure en haut',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'padding_right',
            'attributes' => array(
                'id' => 'label-padding_right',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Marge intérieure à droite',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'padding_bottom',
            'attributes' => array(
                'id' => 'label-padding_bottom',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Marge intérieure en bas',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'padding_left',
            'attributes' => array(
                'id' => 'label-padding_left',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Marge intérieure à gauche',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'border',
            'attributes' => array(
                'id' => 'label-border',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Bordure',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'border_dash',
            'attributes' => array(
                'id' => 'label-border_dash',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Trait (plein, tirets ou pointillé)',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'border_width',
            'attributes' => array(
                'id' => 'label-border_width',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Epaisseur du trait de bordure',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Color',
            'name' => 'border_color',
            'attributes' => array(
                'id' => 'label-border_color',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Couleur de la bordure',
                'label_attributes' => array(),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer',
                'id' => 'documentpdf-submit',
                'class' => 'button default submit left-95px'
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
    }

    public function getInputFilterSpecification()
    {
        return array(
            'margin_left' => array(
                'name' => 'margin_left',
                'required' => true,
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
            'margin_top' => array(
                'name' => 'margin_top',
                'required' => true,
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
            'x_space' => array(
                'name' => 'x_space',
                'required' => true,
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
            'y_space' => array(
                'name' => 'y_space',
                'required' => true,
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
            'label_width' => array(
                'name' => 'label_width',
                'required' => true,
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
            'label_height' => array(
                'name' => 'label_height',
                'required' => true,
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
            'cols_number' => array(
                'name' => 'border_width',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\Digits'
                    )
                )
            ),
            'rows_number' => array(
                'name' => 'border_width',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\Digits'
                    )
                )
            ),
            'padding_top' => array(
                'name' => 'padding_top',
                'required' => true,
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
            'padding_right' => array(
                'name' => 'padding_right',
                'required' => true,
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
            'padding_bottom' => array(
                'name' => 'padding_bottom',
                'required' => true,
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
            'padding_left' => array(
                'name' => 'padding_left',
                'required' => true,
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
            'border' => array(
                'name' => 'border',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'Alnum'
                    )
                ),
                'validators' => array(
                    /*array(
                        'name' => 'Zend\Validator\Regex',
                        'options' => array(
                            'pattern' => '(?:0|1|[LTRB]{0,4})'
                        )
                    )*/
                )
            ),
            'border_dash' => array(
                'name' => 'border_dash',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    )
                ),
                'validators' => array(
                    /*array(
                        'name' => 'Zend\Validator\Regex',
                        'options' => array(
                            'pattern' => '(?:0|[1-9],?[1-9])'
                        )
                    )*/
                )
            ),
            'border_width' => array(
                'name' => 'border_width',
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

    public function setData($data)
    {
        $strategieColor = new Color();
        foreach ($data as $key => &$value) {
            if (substr($key, - 6) == '_color') {
                $value = $strategieColor->hydrate($value);
            }
        }
        parent::setData($data);
    }

    public function setMaxLength()
    {}
}