<?php
/**
 * Formulaire permettant de choisir un bordereau à éditer, à supprimer ou à clôturer
 *
 * Le select a pour clé une chaine construite par concaténation de la dateBordereau et du codeModeDePaiement, séparés par |
 * (voir SbmCommun\Model\Db\Service\Select\BordereauxForSelect et sa méthode decode() pour retrouver les deux paramètres)
 * 
 * @project sbm
 * @package SbmGestion/Form/Finances
 * @filesource BordereauRemiseValeurChoix.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmGestion\Form\Finances;

use SbmCommun\Form\AbstractSbmForm;

class BordereauRemiseValeurChoix extends AbstractSbmForm
{

    public function __construct()
    {
        parent::__construct('bordereau');
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'type' => 'Zend\Form\Element\Select',
                'name' => 'bordereau',
                'attributes' => [
                    'id' => 'bordereau'
                ],
                'options' => [
                    'label' => 'Quel bordereau ?',
                    'label_attributes' => [
                        'class' => 'sbm-label-105dem'
                    ],
                    'empty_option' => 'Choisissez dans la liste',
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
                    'id' => 'editer-cancel',
                    'autofocus' => 'autofocus',
                    'class' => 'button default cancel'
                ]
            ]);
        $this->add(
            [
                'name' => 'editer',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Editer le bordereau',
                    'id' => 'editer-submit',
                    'class' => 'button default submit'
                ]
            ]);
        
        $this->add(
            [
                'name' => 'supprimer',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Supprimer le bordereau',
                    'id' => 'supprimer-submit',
                    'class' => 'button default submit'
                ]
            ]);
        
        $this->add(
            [
                'name' => 'cloturer',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Clôturer le bordereau',
                    'id' => 'cloturer-submit',
                    'class' => 'button default submit'
                ]
            ]);
    }
}