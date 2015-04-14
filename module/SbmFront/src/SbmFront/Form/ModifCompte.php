<?php
/**
 * Formulaire de modification d'un compte
 *
 * Les seuls champs modifiés par ce formulaire sont le titre, le nom et le prénom.
 * 
 * Il n'y a pas besoin de getInputFilterSpecification() car les éléments SbmCommun\Form\Element\NomPropre 
 * et SbmCommun\Form\Element\Prenom ont leur propre méthode getInputSpecification()
 * 
 * @project sbm
 * @package SbmFront/Form
 * @filesource ModifCompte.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 févr. 2015
 * @version 2015-1
 */
namespace SbmFront\Form;

use SbmCommun\Form\AbstractSbmForm;

class ModifCompte extends AbstractSbmForm
{
    private $categorieId;
    public function __construct($categorieId, $param = 'compte')
    {
        $this->categorieId = $categorieId;
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'userId',
            'type' => 'hidden',
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
            'name' => 'titre',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'responsable-titre',
                'class' => 'sbm-select1'
            ),
            'options' => array(
                'label' => 'Votre identité',
                'label_attributes' => array(
                    'class' => 'sbm-label-page1'
                ),
                'value_options' => array(
                    'M.' => 'Monsieur',
                    'Mme' => 'Madame',
                    'Mlle' => 'Mademoiselle',
                    'Dr' => 'Docteur',
                    'Me' => 'Maître',
                    'Pr' => 'Professeur'
                ),
                'empty_option' => 'Choisissez la civilité',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'nom',
            'type' => 'SbmCommun\Form\Element\NomPropre',
            'attributes' => array(
                'id' => 'responsable-nom',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Nom',
                'label_attributes' => array(
                    'class' => 'sbm-label-page1 align-right'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'prenom',
            'type' => 'SbmCommun\Form\Element\Prenom',
            'attributes' => array(
                'id' => 'responsable-prenom',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Prénom',
                'label_attributes' => array(
                    'class' => 'sbm-label-page1 align-right'
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
                'value' => 'Enregistrer les modifications',
                'id' => 'responsable-submit',
                'autofocus' => 'autofocus',
                'class' => 'button submit left-95px'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'responsable-cancel',
                'class' => 'button cancel left-10px'
            )
        ));
        
    }
}