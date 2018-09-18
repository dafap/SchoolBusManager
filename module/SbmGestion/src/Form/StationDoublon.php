<?php
/**
 * Formulaire permettantd de remplacer une station par une autre
 *
 * @project sbm
 * @package SbmGestion/Form
 * @filesource StationDoublon.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmGestion\Form;

use SbmCommun\Form\AbstractSbmForm as Form;
use Zend\InputFilter\InputFilterProviderInterface;

class StationDoublon extends Form implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('doublon');
        $this->setAttribute('method', 'post');

        $this->add([
            'name' => 'page',
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
                'name' => 'stationASupprId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'station-a-supprId',
                    'class' => 'sbm-width-55c'
                ],
                'options' => [
                    'label' => 'Point d\'arrêt à supprimer',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Quel point d\'arrêt ?',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'stationAGarderId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'station-a-garderId',
                    'class' => 'sbm-width-55c'
                ],
                'options' => [
                    'label' => 'Point d\'arrêt à garder',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Quel point d\'arrêt ?',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'decision-cancel',
                    'autofocus' => 'autofocus',
                    'class' => 'button default cancel left-10px'
                ]
            ]);
        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Valider',
                    'id' => 'decision-submit',
                    'class' => 'button default submit left-10px'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'stationASupprId' => [
                'name' => 'stationASupprId',
                'required' => true
            ],
            'stationAGarderId' => [
                'name' => 'stationAGarderId',
                'required' => true
            ]
        ];
    }
}