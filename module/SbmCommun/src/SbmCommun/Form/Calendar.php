<?php
/**
 * Formulaire de saisie et modification d'un élément de `calendar`
 *
 * @project sbm
 * @package SbmCommun/Form
 * @filesource Calendar.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2016
 * @version 2016-2
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Calendar extends AbstractSbmForm implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('calendar');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'calendarId',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'millesime',
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
            'name' => 'description',
            'type' => 'text',
            'attributes' => array(
                'id' => 'calendar-description',
                'class' => 'sbm-width-55c'
            ),
            'options' => array(
                'label' => 'Description',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'dateDebut',
            'type' => 'Zend\Form\Element\DateSelect',
            'attributes' => array(
                'id' => 'calendar-dateDebut',
            ),
            'options' => array(
                'label' => 'Date de début',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'create_empty_option' => true,
                'min_year' => date('Y') - 20,
                'max_year' => date('Y') + 1,
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'dateFin',
            'type' => 'Zend\Form\Element\DateSelect',
            'attributes' => array(
                'id' => 'calendar-dateFin',
            ),
            'options' => array(
                'label' => 'Date de fin',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'create_empty_option' => true,
                'min_year' => date('Y') - 20,
                'max_year' => date('Y') + 2,
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'echeance',
            'type' => 'Zend\Form\Element\DateSelect',
            'attributes' => array(
                'id' => 'calendar-echeance',
            ),
            'options' => array(
                'label' => 'Echéance',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'create_empty_option' => true,
                'min_year' => date('Y') - 20,
                'max_year' => date('Y') + 2,
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'exercice',
            'type' => 'text',
            'attributes' => array(
                'id' => 'calendar-exercice',
                'class' => 'sbm-width-5c'
            ),
            'options' => array(
                'label' => 'Exercice budgétaire',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                'id' => 'calendar-submit',
                'autofocus' => 'autofocus',
                'class' => 'button default submit left-95px'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'calendar-cancel',
                'class' => 'button default cancel'
            )
        ));
    }
    
    public function getInputFilterSpecification()
    {
        return array(
            'description' => array(
                'name' => 'description',
                'required' => true
            ),
            'dateDebut' => array(
                'name' => 'dateDebut',
                'required' => true
            ),
            'dateFin' => array(
                'name' => 'dateFin',
                'required' => true
            ),
            'echeance' => array(
                'name' => 'echeance',
                'required' => true
            )
        );
    }
}