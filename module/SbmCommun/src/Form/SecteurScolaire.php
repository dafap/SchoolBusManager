<?php
/**
 * Formulaire de gestion des secteurs scolaires
 *
 * @project sbm
 * @package SbmCommun/Form
 * @filesource SecteurScolaire.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 sept.2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Form;

class SecteurScolaire extends AbstractSbmForm
{

    public function __construct()
    {
        parent::__construct('secteur-scolaire');
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
        $this->add(
            [
                'name' => 'communeId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'communeIdElement',
                    'class' => 'sbm-width-45c'
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
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Enregistrer',
                    'id' => 'secteur-scolaire-submit',
                    'class' => 'button default submit'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'secteur-scolaire-cancel',
                    'class' => 'button default cancel left-10px'
                ]
            ]);
    }
}