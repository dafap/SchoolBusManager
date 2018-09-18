<?php
/**
 * Formulaire de saisie et modification d'un élève
 *
 * CE FORMULAIRE N'EST PAS UTILISE
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Eleve.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2016
 * @version 2016-2
 */
namespace SbmCommun\Form;

class Eleve extends AbstractSbmForm
{

    public function __construct()
    {
        parent::__construct('eleve');
        $this->setAttribute('method', 'post');
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
            'name' => 'eleveId',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'selection',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'id' => 'eleve-selection'
            ),
            'options' => array(
                'label' => 'Sélectionné',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0'
            )
        ));
        $this->add(array(
            'name' => 'nom',
            'type' => 'text',
            'attributes' => array(
                'id' => 'eleve-nom',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Nom de l\'élève',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'prenom',
            'type' => 'text',
            'attributes' => array(
                'id' => 'eleve-prenom',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Prénom de l\'élève',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'dateN',
            'type' => 'Zend\Form\Element\Date',
            'attributes' => array(
                'id' => 'eleve-dateN',
                'class' => 'sbm-text15'
            ),
            'options' => array(
                'label' => 'Date de naissance',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));

        $this->add(array(
            'name' => 'responsable1Id',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-responsable1Id',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Responsable n°1',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'empty_option' => 'Choisissez un responsable',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'responsable2Id',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-responsable2Id',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Responsable n°2',
                'label_attributes' => array(
                    'class' => 'sbm-label150'
                ),
                'empty_option' => 'Choisissez un responsable',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'responsableFId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-responsableFId',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Responsable financier',
                'label_attributes' => array(
                    'class' => 'sbm-label150'
                ),
                'empty_option' => 'Choisissez un responsable',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'note',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => array(
                'id' => 'eleve-note',
                'class' => 'sbm-note'
            ),
            'options' => array(
                'label' => 'Notes',
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
                'id' => 'eleve-submit',
                'autofocus' => 'autofocus',
                'class' => 'button submit left135'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'eleve-cancel',
                'class' => 'button cancel'
            )
        ));
        
        $this->getInputFilter()->get('responsable2Id')->setRequired(false);
        //$this->getInputFilter()->get('responsableFId')->setRequired(false);
    }
}