<?php
/**
 * Formulaire de choix d'un établissement pour accéder à son portail en tant que cet établissement.
 * Cela peut se faire depuis le portail de l'organisateur ou depuis le portail de gestion.
 *
 * @project sbm
 * @package SbmPortail/src/Form
 * @filesource LikeEtablissement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 mars 2021
 * @version 2021-2.6.1
 */
namespace SbmPortail\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class LikeEtablissement extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('likeetablissement');
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'name' => 'nature',
                'type' => 'hidden',
                'attributes' => [
                    'value' => 'etablissement'
                ]
            ])
            ->add(
            [
                'name' => 'userId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'userId',
                    'class' => 'sbm-width-30c',
                    'autofocus' => 'autofocus'
                ],
                'options' => [
                    'label' => 'Choix de l\'établissement',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => 'Votre choix ?',
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ])
            ->add(
            [
                'name' => 'submit',
                'type' => 'submit',
                'attributes' => [
                    'id' => 'submit',
                    'class' => 'button default submit',
                    'value' => 'Accéder'
                ]
            ])
            ->add(
            [
                'name' => 'cancel',
                'type' => 'submit',
                'attributes' => [
                    'id' => 'cancel',
                    'class' => 'button default cancel',
                    'value' => 'Abandonner'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'userId' => [
                'name' => 'userId',
                'required' => false
            ]
        ];
    }
}