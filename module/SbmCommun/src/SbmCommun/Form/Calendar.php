<?php
/**
 * Formulaire de saisie et modification d'un élément de `calendar`
 *
 * @project sbm
 * @package SbmCommun/Form
 * @filesource Calendar.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 nov. 2014
 * @version 2014-1
 */
namespace SbmCommun\Form;

class Calendar extends AbstractSbmForm
{
    public function __construct($param = 'calendar')
    {
        parent::__construct($param);
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
                'class' => 'sbm-text255'
            ),
            'options' => array(
                'label' => 'Description',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'dateDebut',
            'type' => 'Zend\Form\Element\Date',
            'attributes' => array(
                'id' => 'calendar-dateDebut',
                'class' => 'sbm-text15'
            ),
            'options' => array(
                'label' => 'Date de début',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'dateFin',
            'type' => 'Zend\Form\Element\Date',
            'attributes' => array(
                'id' => 'calendar-dateFin',
                'class' => 'sbm-text15'
            ),
            'options' => array(
                'label' => 'Date de fin',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'echeance',
            'type' => 'Zend\Form\Element\Date',
            'attributes' => array(
                'id' => 'calendar-echeance',
                'class' => 'sbm-text15'
            ),
            'options' => array(
                'label' => 'Echéance',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
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
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'Exercice budgétaire',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
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
                'class' => 'button submit left135'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'calendar-cancel',
                'class' => 'button cancel'
            )
        ));
    }
}