<?php
/**
 * Formulaire de saisie d'une relation entre un user et un établissement ou un transporteur
 *
 * Selon le paramètre passé au constructeur on présentera un select etablissementId ou transporteurId
 *
 * @project sbm
 * @package SbmAdmin/Form
 * @filesource UserRelation.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr]
 * @date 15 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmAdmin\Form;

use SbmCommun\Form\AbstractSbmForm;

class UserRelation extends AbstractSbmForm
{

    public function __construct($name)
    {
        if (! in_array($name, [
            'commune',
            'etablissement',
            'transporteur'
        ])) {
            throw new DomainException(
                "Les valeurs autorisées sont 'commune', 'etablissement' ou 'transporteur'.");
        }
        $choix = $name == 'commune' ? 'Choisissez une commune': 'Choisissez un ' . $name;
        parent::__construct($name);
        $this->setAttribute('method', 'post');
        $this->add([
            'type' => 'hidden',
            'name' => 'userId'
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
                'name' => $name . 'Id',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'id' => 'user-' . $name . 'Id',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-45c'
                ],
                'options' => [
                    'label' => ucfirst($name),
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'empty_option' => $choix,
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