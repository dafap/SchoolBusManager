<?php
/**
 * Formulaire de saisie et modificationd d'une classe
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Classe.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 janv. 2016
 * @version 2016-1.7.1
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Classe extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct($param = 'classe')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'classeId',
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
            'name' => 'nom',
            'type' => 'text',
            'attributes' => array(
                'id' => 'classe-nom',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Nom de la classe',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'aliasCG',
            'type' => 'text',
            'attributes' => array(
                'id' => 'classe-aliascg',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Libellé complet',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'name' => 'niveau',
            'attributes' => array(
                'id' => 'classe-niveau',
                'class' => 'sbm-multicheckbox'
            ),
            'options' => array(
                'label' => 'Cochez les niveaux concernés',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'suivantId',
            'attributes' => array(
                'id' => 'classe-suivantId'
            ),
            'options' => array(
                'label' => 'Indiquez la classe suivante',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'empty_option' => 'Aucune',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer',
                'id' => 'classe-submit',
                'autofocus' => 'autofocus',
                'class' => 'button default submit'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'classe-cancel',
                'class' => 'button default cancel'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'nom' => array(
                'name' => 'nom',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'aliasCG' => array(
                'name' => 'aliasCG',
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
            'suivantId' => array(
                'name' => 'suivantId',
                'required' => false
            )
        );
    }
}