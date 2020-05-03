<?php
/**
 * Formulaire de saisie et modification d'un lien etablissement-station
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource EtablissementStation.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class EtablissementStation extends AbstractSbmForm implements
    InputFilterProviderInterface
{

    public function __construct($name = 'etablissement', $options = [])
    {
        parent::__construct($name, $options);
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
        $this->add([
            'name' => 'origine',
            'type' => 'hidden'
        ]);
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
                'name' => 'stationId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'stationIdElement',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => 'station',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Choisissez une station',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'rang',
                'type' => 'SbmCommun\Form\Element\IsInt',
                'attributes' => [
                    'id' => 'rang',
                    'class' => 'sbm-width-5c'
                ],
                'options' => [
                    'label' => 'Rang de recherche',
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

    public function getInputFilterSpecification()
    {
        return [
            'stationId' => [
                'name' => 'stationId',
                'required' => true
            ]
        ];
    }
}