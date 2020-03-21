<?php
/**
 * Formulaire permettant de choisir à partir que quelle source on doit dupliquer le réseau
 * de transport (lignes, services, etablissements-services, circuits)
 *
 * @project sbm
 * @package SbmGestion\src\Form
 * @filesource DupliquerReseau.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class DupliquerReseau extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct($name = null, $options = [])
    {
        if (! $name) {
            $name = 'dupliquer_reseau';
        }
        parent::__construct($name, $options);
        $this->add(
            [
                'name' => 'millesime_source',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'millesime_source',
                    'class' => 'sbm-select',
                    'autofocus' => 'autofocus'
                ],
                'options' => [
                    'label' => 'Indiquez l\'année à dupliquer',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Année d\'origine ?',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'millesime_nouveau',
                'type' => 'hidden'
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'type' => 'submit',
                'attributes' => [
                    'id' => 'btncancel',
                    'class' => 'button default cancel',
                    'value' => 'Abandonner'
                ]
            ]);
        $this->add(
            [
                'name' => 'submit',
                'type' => 'submit',
                'attributes' => [
                    'id' => 'btnsubmit',
                    'class' => 'button default submit',
                    'value' => 'Lancer la création'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'millesime_source' => [
                'name' => 'millesime_source',
                'required' => true
            ],
            'millesime_nouveau' => [
                'name' => 'millesime_nouveau',
                'required' => true
            ]
        ];
    }
}