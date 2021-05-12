<?php
/**
 * Formulaire de choix de commune pour accéder à son portail en tant que cette commune.
 * Cela peut se faire depuis le portail de l'organisateur ou depuis le portail de gestion.
 *
 * @project sbm
 * @package SbmPortail/src/Form
 * @filesource LikeCommune.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 mars 2021
 * @version 2021-2.6.1
 */
namespace SbmPortail\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class LikeCommune extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('likecommune');
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'name' => 'nature',
                'type' => 'hidden',
                'attributes' => [
                    'value' => 'commune'
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
                    'label' => 'Choix de la commune',
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