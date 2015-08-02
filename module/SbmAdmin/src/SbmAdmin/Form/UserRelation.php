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
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2015
 * @version 2015-1
 */
namespace SbmAdmin\Form;

use SbmCommun\Form\AbstractSbmForm;

class UserRelation extends AbstractSbmForm
{

    public function __construct($name)
    {
        parent::__construct($name);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'type' => 'hidden',
            'name' => 'userId'
        ));
        $this->add(array(
            'name' => 'csrf',
            'type' => 'Zend\Form\Element\Csrf',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 180
                )
            )
        ));
        $this->add(array(
            'name' => $name . 'Id',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'user-' . $name . 'Id',
                'autofocus' => 'autofocus',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => ucfirst($name),
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Choisissez un ' . $name,
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer',
                'id' => 'station-submit',
                'class' => 'button default submit'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'station-cancel',
                'class' => 'button default cancel'
            )
        ));
    }
}