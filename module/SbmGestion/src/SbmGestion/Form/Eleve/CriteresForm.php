<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource CriteresForm.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2015
 * @version 2015-1
 */
namespace SbmGestion\Form\Eleve;

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
            'name' => 'responsable',
            'attributes' => array(
                'id' => 'critere-responsable',
                'maxlength' => '45',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Responsable',
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
                'label_attributes' => array(
                    'class' => 'sbm-new-line'
                ),
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
            'name' => 'etat',
            'attributes' => array(
                'id' => 'critere-etat',
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Etat',
                'label_attributes' => array(
                    'class' => 'sbm-new-line'
                ),
                'empty_option' => 'Tout',
                'value_options' => array(
                    '1' => 'Incrits',
                    '2' => 'Préinscrits',
                    '3' => 'Rayés',
                    '4' => 'Famille d\'accueil'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'demande',
            'attributes' => array(
                'id' => 'critere-demande',
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Demandes',
                'empty_option' => 'Tout',
                'value_options' => array(
                    '1' => 'Non traitées',
                    '2' => 'Partiellement traitées',
                    '3' => 'Traitées'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'decision',
            'attributes' => array(
                'id' => 'critere-decision',
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Décisions',
                'empty_option' => 'Tout',
                'value_options' => array(
                    '1' => 'Accord total',
                    '2' => 'Accord partiel',
                    '3' => 'Subvention',
                    '4' => 'Refus'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'derogation',
            'attributes' => array(
                'type' => 'checkbox',
                'useHiddenElement' => false,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Dérogation',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'selection',
            'attributes' => array(
                'type' => 'checkbox',
                'useHiddenElement' => false,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Sélectionnés',
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
            'etablissementId' => array(
                'required' => false
            ),
            'classeId' => array(
                'required' => false
            ),
            'etat' => array(
                'required' => false
            ),
            'demande' => array(
                'required' => false
            ),
            'decision' => array(
                'required' => false
            ),
            'derogation' => array(
                'required' => false
            ),
            'selection' => array(
                'required' => false
            )
        );
    }
}