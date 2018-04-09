<?php
/**
 * Formulaire de saisie et modification d'un lien etablissement-service
 * 
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource EtablissementService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Form;

class EtablissementService extends AbstractSbmForm
{

    /**
     * Désigne la colonne qui sera dans un Select.
     * L'autre sera dans un hidden.
     *
     * @var string
     */
    private $select;

    public function __construct($select = 'service', $param = 'etablissement')
    {
        $this->select = $select;
        parent::__construct($param);
        $this->setAttribute('method', 'post');
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
                'name' => 'origine',
                'type' => 'hidden'
            ]);
        if ($select == 'service') {
            $this->add(
                [
                    'name' => 'etablissementId',
                    'type' => 'hidden',
                    'attributes' => [
                        'id' => 'etablissementIdElement'
                    ]
                ]);
            $this->add(
                [
                    'name' => 'serviceId',
                    'type' => 'Zend\Form\Element\Select',
                    'attributes' => [
                        'id' => 'serviceIdElement',
                        'autofocus' => 'autofocus',
                        'class' => 'sbm-width-45c'
                    ],
                    'options' => [
                        'label' => 'Service',
                        'label_attributes' => [
                            'class' => 'sbm-label'
                        ],
                        'empty_option' => 'Choisissez un service',
                        'error_attributes' => [
                            'class' => 'sbm-error'
                        ]
                    ]
                ]);
        } else {
            $this->add(
                [
                    'name' => 'serviceId',
                    'type' => 'hidden',
                    'attributes' => [
                        'id' => 'serviceIdElement'
                    ]
                ]);
            $this->add(
                [
                    'name' => 'etablissementId',
                    'type' => 'Zend\Form\Element\Select',
                    'attributes' => [
                        'id' => 'etablissementIdElement',
                        'autofocus' => 'autofocus',
                        'class' => 'sbm-width-45c'
                    ],
                    'options' => [
                        'label' => 'Etablissement',
                        'label_attributes' => [
                            'class' => 'sbm-label'
                        ],
                        'empty_option' => 'Choisissez un établissement',
                        'error_attributes' => [
                            'class' => 'sbm-error'
                        ]
                    ]
                ]);
        }
        $this->add(
            [
                'name' => 'stationId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'stationIdElement',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'Station desservant l\'établissement',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez une station',
                    'disable_inarray_validator' => true,
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
                    'id' => 'station-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'station-cancel',
                    'class' => 'button default cancel'
                ]
            ]);
    }
}