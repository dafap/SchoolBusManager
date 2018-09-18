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
 * @date 13 avr. 2016
 * @version 2016-2
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
        $this->add([
            'name' => 'csrf',
            'type' => 'Zend\Form\Element\Csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 180
                ]
            ]
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'docaffectationId'
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
        $this->add([
            'type' => 'text',
            'name' => 'libelle',
            'attributes' => [
                'autofocus' => 'autofocus',
                'id' => 'libelle',
                'class' => 'sbm-width-55c'
            ],
            'options' => [
                'label' => 'Libellé dans le menu',
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Select',
            'name' => 'route',
            'attributes' => [
                'id' => 'route'
            ],
            'options' => [
                'label' => 'Page du site',
                'empty_option' => 'Choisissez',
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'text',
            'name' => 'ordinal_position',
            'attributes' => [
                'id' => 'ordinal_position'
            ],
            'options' => [
                'label' => 'Position dans le menu',
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Enregistrer',
                'id' => 'docaffectation-submit',
                'class' => 'button default submit'
            ]
        ]);
        $this->add([
            'type' => 'submit',
            'name' => 'cancel',
            'attributes' => [
                'value' => 'Abandonner',
                'id' => 'docaffectation-cancel',
                'class' => 'button default cancel'
            ]
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'libelle' => [
                'name' => 'libelle',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'ordinal_position' => [
                'name' => 'ordinal_position',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'Digits'
                    ]
                ]
            ]
        ];
    }
}