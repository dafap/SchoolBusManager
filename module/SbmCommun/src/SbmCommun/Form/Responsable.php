<?php
/**
 * Formulaire de saisie et modification d'un responsable
 *
 * A noter que les éléments SbmCommun\Form\Element\NomPropre et SbmCommun\Form\Element\Prenom ont leur propre méthode getInputSpecification()
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
use Zend\InputFilter\InputFilterProviderInterface;

class Responsable extends AbstractSbmForm implements InputFilterProviderInterface
{
    /**
     * Indicateur
     * 
     * @var bool
     */
    private $verrouille;

    /**
     * Constructeur
     * 
     * @param boolean $option
     *            indique si l'identité doit être verrouillée en lecture seule
     */
    public function __construct($option = false)
    {
        $this->verrouille = $option;
        parent::__construct('responsable');
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
            'name' => 'userId',
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
                'class' => 'sbm-width-15c'
            ),
            'options' => array(
                'label' => 'Identité du responsable',
                'label_attributes' => array(
                    'class' => 'sbm-label responsable-titre'
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
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Nom',
                'label_attributes' => array(
                    'class' => 'sbm-label responsable-nom'
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
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Prénom',
                'label_attributes' => array(
                    'class' => 'sbm-label responsable-prenom'
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
                'id' => 'responsable-titre2',
                'class' => 'sbm-width-15c'
            ),
            'options' => array(
                'label' => 'Conjoint',
                'label_attributes' => array(
                    'class' => 'sbm-label help'
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
                'id' => 'responsable-nom2',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Nom',
                'label_attributes' => array(
                    'class' => 'sbm-label responsable-nom'
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
                'id' => 'responsable-prenom2',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Prénom',
                'label_attributes' => array(
                    'class' => 'sbm-label responsable-prenom'
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
                'class' => 'sbm-width-40c'
            ),
            'options' => array(
                'label' => 'Adresse',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'adresseL2',
            'type' => 'SbmCommun\Form\Element\Adresse',
            'attributes' => array(
                'id' => 'responsable-adresseL2',
                'class' => 'sbm-width-40c'
            ),
            'options' => array(
                'label' => 'Complément d\'adresse',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                'class' => 'sbm-width-5c'
            ),
            'options' => array(
                'label' => 'Code postal',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Commune',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                'class' => 'sbm-width-15c'
            ),
            'options' => array(
                'label' => 'Téléphone domicile',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                'class' => 'sbm-width-15c'
            ),
            'options' => array(
                'label' => 'Téléphone portable',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                'class' => 'sbm-width-15c'
            ),
            'options' => array(
                'label' => 'Autre téléphone',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                'id' => 'respondable-email',
                'class' => 'sbm-width-50c'
            ),
            'options' => array(
                'label' => 'Email',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'ancienAdresseL1',
            'type' => 'SbmCommun\Form\Element\Adresse',
            'attributes' => array(
                'id' => 'responsable-ancienAdresseL1',
                'class' => 'sbm-width-40c'
            ),
            'options' => array(
                'label' => 'Ancienne adresse',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'ancienAdresseL2',
            'type' => 'SbmCommun\Form\Element\Adresse',
            'attributes' => array(
                'id' => 'responsable-ancienAdresseL2',
                'class' => 'sbm-width-40c'
            ),
            'options' => array(
                'label' => 'Ancienne adresse',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                'class' => 'sbm-width-5c'
            ),
            'options' => array(
                'label' => 'Ancien code postal',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Ancienne commune',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                    'class' => 'sbm-label'
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
                'id' => 'responsable-dateDemenagement'
            ),
            'options' => array(
                'label' => 'Date du déménagement',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                    'class' => 'sbm-label'
                ),
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0'
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'note',
            'attributes' => array(
                'id' => 'responsable-note'
            ),
            'options' => array(
                'label' => 'Notes',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
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
                'id' => 'responsable-submit',
                'autofocus' => 'autofocus',
                'class' => 'button default submit'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'responsable-cancel',
                'class' => 'button default cancel'
            )
        ));
        
        if ($this->verrouille) {
            $this->verrouilleIdentity();
        }
    }

    public function getInputFilterSpecification()
    {
        $spec = array(
            'titre2' => array(
                'name' => 'titre2',
                'required' => false
            ),
            'nom2' => array(
                'name' => 'nom2',
                'required' => false
            ),
            'prenom2' => array(
                'name' => 'prenom2',
                'required' => false
            ),
            'adresseL1' => array(
                'name' => 'adresseL1',
                'required' => true
            ),
            'codePostal' => array(
                'name' => 'codePostal',
                'required' => true
            ),
            'ancienAdresseL1' => array(
                'name' => 'ancienAdresseL1',
                'required' => false
            ),
            'ancienAdresseL2' => array(
                'name' => 'ancienAdresseL2',
                'required' => false
            ),
            'ancienCommuneId' => array(
                'name' => 'ancienCommuneId',
                'required' => false
            ),
            'ancienCodePostal' => array(
                'name' => 'ancienCodePostal',
                'required' => false
            ),
            'telephoneF' => array(
                'name' => 'telephoneF',
                'required' => false
            ),
            'telephoneP' => array(
                'name' => 'telephoneP',
                'required' => false
            ),
            'telephoneT' => array(
                'name' => 'telephoneT',
                'required' => false
            ),
            'email' => array(
                'name' => 'email',
                'required' => false
            ),
            'dateDemenagement' => array(
                'name' => 'dateDemenagement',
                'required' => false
            ),
            'selection' => array(
                'name' => 'selection',
                'required' => false
            )
        );
        if ($this->verrouille) {
            $spec['titre'] = array(
                'name' => 'adresseL1',
                'required' => false
            );
            $spec['titre'] = array(
                'name' => 'codePostal',
                'required' => false
            );
            $spec['titre'] = array(
                'name' => 'titre',
                'required' => false
            );
            $spec['demenagement'] = array(
                'name' => 'demenagement',
                'required' => false
            );
        }
        return $spec;
    }

    private function verrouilleIdentity()
    {
        foreach (array(
            'titre' => 'disabled',
            'nom' => 'readonly',
            'prenom' => 'readonly',
            'email' => 'readonly'
        ) as $elementName => $attr) {
            $e = $this->get($elementName);
            $e->setAttribute($attr, $attr);
        }
    }
    
    public function isValid()
    {
        $result = parent::isValid();
        // un des 3 numéros de téléphones doit être renseigné
        if (empty($this->data['telephoneF']) && empty($this->data['telephoneP']) && empty($this->data['telephoneT'])) {
            $result = false;
            $element = $this->get('telephoneT');
            $element->setMessages(array('Vous devez indiquer au moins un numéro de téléphone où l\'on pourra vous joindre.'));
        }
        return $result;
    }
}