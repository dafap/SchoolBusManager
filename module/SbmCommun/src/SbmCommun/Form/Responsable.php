<?php
/**
 * Formulaire de saisie et modification d'un responsable
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Responsable.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 juin 2014
 * @version 2014-1
 */
namespace SbmCommun\Form;

use Zend\Filter\StringToUpper;
use Zend\Filter\StripTags;
use Zend\Filter\StringTrim;

class Responsable extends AbstractSbmForm
{

    public function __construct($param = 'responsable')
    {
        parent::__construct($param);
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
            'name' => 'responsableId',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'nature',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'titre',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'responsable-titre',
                'class' => 'sbm-select1'
            ),
            'options' => array(
                'label' => 'Identité du responsable',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
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
                    'class' => 'sbm-label70 margin-left20'
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
                    'class' => 'sbm-label90 margin-left20'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'titre2',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'responsable-titre',
                'class' => 'sbm-select1'
            ),
            'options' => array(
                'label' => 'Autre identité à la même adresse',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
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
            'name' => 'nom2',
            'type' => 'SbmCommun\Form\Element\NomPropre',
            'attributes' => array(
                'id' => 'responsable-nom',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Autre nom',
                'label_attributes' => array(
                    'class' => 'sbm-label70 margin-left20'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'prenom2',
            'type' => 'SbmCommun\Form\Element\Prenom',
            'attributes' => array(
                'id' => 'responsable-prenom',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Autre prénom',
                'label_attributes' => array(
                    'class' => 'sbm-label90 margin-left20'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'adresseL1',
            'type' => 'SbmCommun\Form\Element\Adresse',
            'attributes' => array(
                'id' => 'responsable-adresseL1',
                'class' => 'sbm-text38'
            ),
            'options' => array(
                'label' => 'Adresse',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'adresseL2',
            'type' => 'text',
            'attributes' => array(
                'id' => 'responsable-adresseL2',
                'class' => 'sbm-text38'
            ),
            'options' => array(
                'label' => 'Adresse',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'codePostal',
            'type' => 'SbmCommun\Form\Element\CodePostal',
            'attributes' => array(
                'id' => 'responsable-codePostal',
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'Code postal',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'communeId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'responsable-communeId',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Commune',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez une commune',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'telephoneF',
            'type' => 'SbmCommun\Form\Element\Telephone',
            'attributes' => array(
                'id' => 'respondable-telephoneF',
                'class' => 'sbm-text14'
            ),
            'options' => array(
                'label' => 'Téléphone domicile',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'telephoneP',
            'type' => 'SbmCommun\Form\Element\Telephone',
            'attributes' => array(
                'id' => 'respondable-telephoneP',
                'class' => 'sbm-text14'
            ),
            'options' => array(
                'label' => 'Téléphone portable',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'telephoneT',
            'type' => 'SbmCommun\Form\Element\Telephone',
            'attributes' => array(
                'id' => 'respondable-telephoneT',
                'class' => 'sbm-text14'
            ),
            'options' => array(
                'label' => 'Téléphone travail',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'email',
            'type' => 'Zend\Form\Element\Email',
            'attributes' => array(
                'id' => 'respondable-telephone',
                'class' => 'sbm-text50'
            ),
            'options' => array(
                'label' => 'Email',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'ancienAdresseL1',
            'type' => 'text',
            'attributes' => array(
                'id' => 'responsable-ancienAdresseL1',
                'class' => 'sbm-text38'
            ),
            'options' => array(
                'label' => 'Ancienne adresse',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'ancienAdresseL2',
            'type' => 'text',
            'attributes' => array(
                'id' => 'responsable-ancienAdresseL2',
                'class' => 'sbm-text38'
            ),
            'options' => array(
                'label' => 'Ancienne adresse',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'ancienCodePostal',
            'type' => 'SbmCommun\Form\Element\CodePostal',
            'attributes' => array(
                'id' => 'responsable-ancienCodePostal',
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'Ancien code postal',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'ancienCommuneId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'responsable-ancienCommuneId',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Ancienne commune',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez une commune',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'demenagement',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'id' => 'responsable-demenagement'
            ),
            'options' => array(
                'label' => 'Déménagement',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0'
            )
        ));
        $this->add(array(
            'name' => 'dateDemenagement',
            'type' => 'Zend\Form\Element\Date',
            'attributes' => array(
                'id' => 'responsable-dateDemenagement',
            ),
            'options' => array(
                'label' => 'Date du déménagement',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'format' => 'Y-m-d'
            )
        ));
        $this->add(array(
            'name' => 'selection',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'id' => 'responsable-selection'
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
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer',
                'id' => 'responsable-submit',
                'autofocus' => 'autofocus',
                'class' => 'button submit left135'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'responsable-cancel',
                'class' => 'button cancel'
            )
        ));
        $input_filter = $this->getInputFilter();
        // $input_filter->get('nom')->setRequired(true)->getFilterChain()->attach(new StripTags())->attach(new StringToUpper())->attach(new StringTrim());
        
        $input_filter->get('ancienAdresseL1')->setRequired(false);
        $input_filter->get('ancienAdresseL2')->setRequired(false);
        $input_filter->get('ancienCommuneId')->setRequired(false);
        $input_filter->get('ancienCodePostal')->setRequired(false);
        $input_filter->get('telephoneF')->setRequired(false);
        $input_filter->get('telephoneP')->setRequired(false);
        $input_filter->get('telephoneT')->setRequired(false);
        $input_filter->get('email')->setRequired(false);
        $input_filter->get('dateDemenagement')->setRequired(false);
    }
}