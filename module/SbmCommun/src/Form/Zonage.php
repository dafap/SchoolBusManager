<?php
/**
 * Formulaire de saisie et modification d'un élément de `calendar`
 *
 * @project sbm
 * @package SbmCommun/src/Form
 * @filesource Zonage.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 août 2019
 * @version 2019-2.5.1
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Zonage extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('zonage');
        $this->setAttribute('method', 'post');
        $this->add([
            'name' => 'zonageId',
            'type' => 'hidden'
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
                'name' => 'communeId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'zonage-communeId'
                ],
                'options' => [
                    'label' => 'Commune',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez une commune',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'nom',
                'type' => 'SbmCommun\Form\Element\Adresse',
                'attributes' => [
                    'id' => 'zonage-nom',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Désignation',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'inscriptionenligne',
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => [
                    'id' => 'zonage-inscriptionenligne',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Inscription en ligne autorisée',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'paiementenligne',
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => [
                    'id' => 'zonage-paiementenligne',
                    'class' => 'sbm-checkbox'
                ],
                'options' => [
                    'label' => 'Paiement en ligne autorisé',
                    'label_attributes' => [
                        'class' => 'sbm-label'
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
                    'id' => 'calendar-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'button default submit left-95px'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'calendar-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'nom' => [
                'name' => 'nom',
                'required' => true
            ]
        ];
    }
}