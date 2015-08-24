<?php
/**
 * Formulaire d'affectation d'un document à une route (ou controller ou vue.phtml)
 *
 * 
 * @project sbm
 * @package SbmPdf/Form
 * @filesource DocAffectation.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 août 2015
 * @version 2015-1
 */
namespace SbmPdf\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class DocAffectation extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('docaffectation');
        $this->setAttribute('method', 'post');
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
            'type' => 'hidden',
            'name' => 'docaffectationId'
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
            'type' => 'text',
            'name' => 'libelle',
            'attributes' => array(
                'autofocus' => 'autofocus',
                'id' => 'libelle',
                'class' => 'sbm-width-55c'
            ),
            'options' => array(
                'label' => 'Libellé dans le menu',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'route',
            'attributes' => array(
                'id' => 'route'
            ),
            'options' => array(
                'label' => 'Page du site',
                'empty_option' => 'Choisissez',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'ordinal_position',
            'attributes' => array(
                'id' => 'ordinal_position'
            ),
            'options' => array(
                'label' => 'Position dans le menu',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Enregistrer',
                'id' => 'docaffectation-submit',
                'class' => 'button default submit'
            )
        ));
        $this->add(array(
            'type' => 'submit',
            'name' => 'cancel',
            'attributes' => array(
                'value' => 'Abandonner',
                'id' => 'docaffectation-cancel',
                'class' => 'button default cancel'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'libelle' => array(
                'name' => 'libelle',
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
            'ordinal_position' => array(
                'name' => 'ordinal_position',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'Digits'
                    )
                )
            )
        );
    }
}