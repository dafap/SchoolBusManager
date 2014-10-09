<?php
/**
 * Formulaire de saisie et modificationd d'une classe
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Classe.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Form;

class Classe extends AbstractSbmForm
{

    public function __construct($param = 'classe')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'classeId',
            'type' => 'hidden'
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
            'name' => 'nom',
            'type' => 'text',
            'attributes' => array(
                'id' => 'classe-nom',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Nom de la classe',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'aliasCG',
            'type' => 'text',
            'attributes' => array(
                'id' => 'classe-aliascg',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Nom CG',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'name' => 'niveau',
            'attributes' => array(
                'id' => 'classe-niveau',
                'class' => 'sbm-multicheckbox'
            ),
            'options' => array(
                'label' => 'Cochez les niveaux concernés',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
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
                'id' => 'classe-submit',
                'autofocus' => 'autofocus',
                'class' => 'button submit left135'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'classe-cancel',
                'class' => 'button cancel'
            )
        ));
    }
}