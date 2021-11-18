<?php
/**
 * Formulaire de saisie d'une station de départ depuis le domicile
 *
 * @project sbm
 * @package SbmGestion/src/Form
 * @filesource StationDepart.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 nov. 2021
 * @version 2021-2.6.4
 */
namespace SbmGestion\Form;

use Zend\InputFilter\InputFilterProviderInterface;
use SbmCommun\Form\AbstractSbmForm as Form;

class StationDepart extends Form implements InputFilterProviderInterface
{
    const SAVE_ONLY = 'save-only';

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
                    //'id' => Pas d'id pour forcer l'encapsulation et pourvoir utiliser un css
                    'class' => 'stationId',
                    'autofocus' => 'autofocus'
                ],
                'options' => [
                    'label' => 'Indiquer la station de départ du domicile (celle qui sera sur le PASS)',
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
                    //'id' => Pas d'id pour forcer l'encapsulation et pourvoir utiliser un css
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
                    'class' => 'button default cancel new-line',
                    'title' =>'Quitte sans rien enregistrer'
                ]
            ]);
        $this->add(
            [
                'name' => self::SAVE_ONLY,
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Enregistrer la station',
                    'id' => 'stationdepart-save-only',
                    'class' => 'button default save-only',
                    'title' => 'Enregistre la station sans modifier les affectations'
                ]
            ]);
        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Lancer la recherche',
                    'id' => 'stationdepart-submit',
                    'class' => 'button default submit',
                    'title' => 'Enregistre la station origine et lance la recherche d\'affectations'
                ]
            ]);
    }
    public function getInputFilterSpecification()
    {
        return [
            'raz' => [
                'name' => 'raz',
                'required' => false
            ]
        ];
    }

}