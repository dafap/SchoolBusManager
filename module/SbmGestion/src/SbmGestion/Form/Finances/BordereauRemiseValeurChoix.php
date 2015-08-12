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
 * @date 11 août 2015
 * @version 2015-1
 */
namespace SbmGestion\Form\Finances;

use SbmCommun\Form\AbstractSbmForm;

class BordereauRemiseValeurChoix extends AbstractSbmForm
{

    public function __construct()
    {
        parent::__construct('bordereau');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'bordereau',
            'attributes' => array(
                'id' => 'bordereau'
            ),
            'options' => array(
                'label' => 'Quel bordereau ?',
                'label_attributes' => array(),
                'empty_option' => 'Choisissez dans la liste',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'editer-cancel',
                'autofocus' => 'autofocus',
                'class' => 'button default cancel'
            )
        ));
        $this->add(array(
            'name' => 'editer',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Editer le bordereau',
                'id' => 'editer-submit',
                'class' => 'button default submit'
            )
        ));

        $this->add(array(
            'name' => 'supprimer',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Supprimer le bordereau',
                'id' => 'supprimer-submit',
                'class' => 'button default submit'
            )
        ));

        $this->add(array(
            'name' => 'cloturer',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Clôturer le bordereau',
                'id' => 'cloturer-submit',
                'class' => 'button default submit'
            )
        ));
    }
}