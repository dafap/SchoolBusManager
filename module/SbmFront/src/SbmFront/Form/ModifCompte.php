<?php
/**
 * Formulaire de modification d'un compte
 *
 * Les seuls champs modifiés par ce formulaire sont le titre, le nom et le prénom.
 * 
 * Il n'y a pas besoin de getInputFilterSpecification() car les éléments SbmCommun\Form\Element\NomPropre 
 * et SbmCommun\Form\Element\Prenom ont leur propre méthode getInputSpecification()
 * 
 * @project sbm
 * @package SbmFront/Form
 * @filesource ModifCompte.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmFront\Form;

use SbmCommun\Form\AbstractSbmForm;

class ModifCompte extends AbstractSbmForm
{

    public function __construct()
    {
        parent::__construct('compte');
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'name' => 'userId',
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
                'name' => 'titre',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'responsable-titre',
                    'class' => 'sbm-select1'
                ],
                'options' => [
                    'label' => 'Votre identité',
                    'label_attributes' => [
                        'class' => 'sbm-label-page1'
                    ],
                    'value_options' => [
                        'M.' => 'Monsieur',
                        'Mme' => 'Madame',
                        'Mlle' => 'Mademoiselle',
                        'Dr' => 'Docteur',
                        'Me' => 'Maître',
                        'Pr' => 'Professeur'
                    ],
                    'empty_option' => 'Choisissez la civilité',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'nom',
                'type' => 'SbmCommun\Form\Element\NomPropre',
                'attributes' => [
                    'id' => 'responsable-nom',
                    'class' => 'sbm-text30'
                ],
                'options' => [
                    'label' => 'Nom',
                    'label_attributes' => [
                        'class' => 'sbm-label-page1 align-right'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'prenom',
                'type' => 'SbmCommun\Form\Element\Prenom',
                'attributes' => [
                    'id' => 'responsable-prenom',
                    'class' => 'sbm-text30'
                ],
                'options' => [
                    'label' => 'Prénom',
                    'label_attributes' => [
                        'class' => 'sbm-label-page1 align-right'
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
                    'value' => 'Enregistrer les modifications',
                    'id' => 'responsable-submit',
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
                    'id' => 'responsable-cancel',
                    'class' => 'button default cancel left-10px'
                ]
            ]);
    }
}