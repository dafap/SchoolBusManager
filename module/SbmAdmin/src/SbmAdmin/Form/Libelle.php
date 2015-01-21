<?php
/**
 * Formulaire de saisie et modification d'un `libelle`
 *
 *
 * @project sbm
 * @package module/SbmAdmin/src/SbmAdmin/Form
 * @filesource Libelle.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 jan. 2015
 * @version 2015-1
 */
namespace SbmAdmin\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;
// use Zend\Validator;
class Libelle extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct($param = 'libelle')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'id',
            'type' => 'hidden'
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
            'name' => 'nature',
            'type' => 'text',
            'attributes' => array(
                'id' => 'libelle-nature',
                'class' => 'sbm-text20'
            ),
            'options' => array(
                'label' => 'Nature',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'code',
            'type' => 'text',
            'attributes' => array(
                'id' => 'libelle-code',
                'class' => 'sbm-text11'
            ),
            'options' => array(
                'label' => 'Code',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'libelle',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => array(
                'id' => 'libelle-libelle',
                'class' => 'sbm-note'
            ),
            'options' => array(
                'label' => 'Libellé',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'ouvert',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'id' => 'libelle-ouvert'
            ),
            'options' => array(
                'label' => 'Ouvert',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0'
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer',
                'id' => 'libelle-submit',
                'autofocus' => 'autofocus',
                'class' => 'button submit left135'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'libelle-cancel',
                'class' => 'button cancel'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'nature' => array(
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'Zend\I18n\Filter\Alnum'
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'Zend\Validator\StringLength',
                        'options' => array(
                            'min' => 1,
                            'max' => 20
                        )
                    )
                )
            ),
            'code' => array(
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\Digits'
                    )
                ),
            )
        );
    }
}