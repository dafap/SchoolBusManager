<?php
/**
 * Formulaire de saisie d'une station de départ depuis le domicile
 *
 * @project sbm
 * @package SbmGestion/src/Form
 * @filesource StationDepart.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmGestion\Form;

use SbmCommun\Form\AbstractSbmForm as Form;

class StationDepart extends Form
{

    public function __construct()
    {
        parent::__construct('stationdepart-form');
        // les hiddens reçus en post et à transmettre à nouveau
        foreach ([
            'millesime',
            'etablissementId',
            'eleveId',
            'trajet',
            'jours',
            'responsableId',
            'regimeId',
            'op'
        ] as $name) {
            $this->add([
                'name' => $name,
                'type' => 'hidden'
            ]);
        }
        $this->add(
            [
                'name' => 'stationId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'stationdepart-stationId',
                    'class' => 'stationId',
                    'autofocus' => 'autofocus'
                ],
                'options' => [
                    'label' => 'Indiquer la station de départ du domicile',
                    'label_attributes' => [
                        'class' => 'sbm-form-auto'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'raz',
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => [
                    'id' => 'affectations-raz',
                    'value' => 0
                ],
                'options' => [
                    'label' => 'Garder les affectations déjà présentes',
                    'label_attributes' => [
                        'class' => 'sbm-form-auto'
                    ],
                    'use_hidden_element' => true,
                    'checked_value' => 'GARDE',
                    'unchecked_value' => 'RAZ',
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
                    'id' => 'stationdepart-cancel',
                    'autofocus' => 'autofocus',
                    'class' => 'button default cancel'
                ]
            ]);
        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Lancer la recherche',
                    'id' => 'stationdepart-submit',
                    'class' => 'button default submit'
                ]
            ]);
    }
}