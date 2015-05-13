<?php
/**
 * Formulaire de création d'un élève (phase 1)
 *
 * La création d'un élève se fait en plusieurs phases :
 * (Form1) : demande du nom, prénom, date de naissance, responsable1, garde_alternée, responsable2 (facultatif)
 * - recherche dans la base s'il existe des élèves ayant ces caractéristiques
 * - si oui, affichage de la liste trouvée (Liste1) avec possibilité de choisir un élève (21) ou de créer un nouvel élève (22)
 * - si non, création d'un nouvel élève (22)
 * (21) : recherche dans la table scolarites en année courante si la fiche existe
 * - si oui, passage en mode modification FIN
 * - si non, création de la scolarite (31)
 * (22) : enregistre le formulaire (Form1) et récupère le eleveId puis création de la scolarité (31)
 * (31) : formulaire (Form2) pour saisir la scolarité (sans les éléments de décision) : etablissement, classe, joursTransport, demandeR1, demandeR2, commentaire
 * - enregistre la scolarité
 * - passe en mode modification FIN
 * 
 * @project sbm
 * @package SbmGestion/Form/Eleve
 * @filesource AddElevePhase1.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 mai 2015
 * @version 2015-1
 */
namespace SbmGestion\Form\Eleve;

use Zend\InputFilter\InputFilterProviderInterface;
use SbmCommun\Form\AbstractSbmForm;

class AddElevePhase1 extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct($options = array())
    {
        parent::__construct('eleve', $options);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'eleveId',
            'type' => 'hidden',
            'attributes' => array(
                'value' => null
            )
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
            'type' => 'SbmCommun\Form\Element\NomPropre',
            'name' => 'nom',
            'attributes' => array(
                'id' => 'eleve-nom',
                'autofocus' => 'autofocus',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Nom de l\'élève',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'SbmCommun\Form\Element\Prenom',
            'name' => 'prenom',
            'attributes' => array(
                'id' => 'eleve-prenom',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Prénom de l\'élève',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Date',
            'name' => 'dateN',
            'attributes' => array(
                'id' => 'eleve-dateN'
            ),
            'options' => array(
                'label' => 'Date de naissance',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                ),
                'format' => 'Y-m-d'
            )
        ));        
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'ga',
            'attributes' => array(
                'id' => 'eleve-ga'
            ),
            'options' => array(
                'label' => 'Garde alternée',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0'
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'responsable1Id',
            'attributes' => array(
                'id' => 'eleve-responsable1Id',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Responsable',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Choisissez un responsable',
                'error_attributes' => array(
                    'class' => 'sbm_error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'responsable2Id',
            'attributes' => array(
                'id' => 'eleve-responsable2Id',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Responsable',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Choisissez un responsable',
                'error_attributes' => array(
                    'class' => 'sbm_error'
                )
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer',
                'id' => 'station-submit',
                'class' => 'button default submit left-95px'
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
    
    /**
     * Description des contraintes, filtres et validateurs
     *
     * (non-PHPdoc)
     *
     * @see \Zend\InputFilter\InputFilterProviderInterface::getInputFilterSpecification()
     */
    public function getInputFilterSpecification()
    {
        return array(
            'responsable2Id' => array(
                'required' => false
            )
        );
    }
    
}