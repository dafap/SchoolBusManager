<?php
/**
 * Formulaire des critères de recherche des élèves pour le portail
 * 
 * @project sbm
 * @package SbmPortail/Form
 * @filesource CriteresForm.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 juillet 2015
 * @version 2015-1
 */
namespace SbmPortail\Form;

use Zend\InputFilter\InputFilterProviderInterface;
use SbmCommun\Form\CriteresForm as SbmCommunCriteresForm;

class CriteresForm extends SbmCommunCriteresForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('criteres');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'type' => 'text',
            'name' => 'numero',
            'attributes' => array(
                'id' => 'critere-nom',
                'maxlength' => '11',
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Numéro',
                'label_attributes' => array(
                    'class' => 'sbm-first'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'nomSA',
            'attributes' => array(
                'id' => 'critere-nom',
                'maxlength' => '45',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Nom',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'prenomSA',
            'attributes' => array(
                'id' => 'critere-prenom',
                'maxlength' => '45',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Prénom',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'responsable',
            'attributes' => array(
                'id' => 'critere-responsable',
                'maxlength' => '45',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Responsable',
                'label_attributes' => array(
                    'class' => 'sbm-new-line'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'etablissementId',
            'attributes' => array(
                'id' => 'critere-etablissementId',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Etablissement',
                'empty_option' => 'Tout',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'classeId',
            'attributes' => array(
                'id' => 'critere-classeId',
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Classe',
                'empty_option' => 'Tout',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'serviceId',
            'attributes' => array(
                'id' => 'critere-serviceId',
                'class' => 'sbm-width-15c'
            ),
            'options' => array(
                'label' => 'Circuit',
                'label_attributes' => array(
                    'class' => 'sbm-new-line'),
                'empty_option' => 'Tous',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'stationId',
            'attributes' => array(
                'id' => 'critere-stationId',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Arrêt',
                'empty_option' => 'Tous',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => array(
                'title' => 'Rechercher',
                'id' => 'criteres-submit',
                'autofocus' => 'autofocus',
                'class' => 'fam-find button submit'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'numero' => array(
                'name' => 'numero',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'nomSA' => array(
                'name' => 'nomSA',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'prenomSA' => array(
                'name' => 'prenomSA',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'responsable' => array(
                'name' => 'responsable',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'etablissementId' => array(
                'name' => 'etablissementId',
                'required' => false
            ),
            'classeId' => array(
                'name' => 'classeId',
                'required' => false
            ),
            'serviceId' => array(
                'name' => 'serviceId',
                'required' => false
            ),
            'stationId' => array(
                'name' => 'stationId',
                'required' => false
            ),
            'etat' => array(
                'name' => 'etat',
                'required' => false
            )
        );
    }
}