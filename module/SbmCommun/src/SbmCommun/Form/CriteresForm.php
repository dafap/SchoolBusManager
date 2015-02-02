<?php
/**
 * Formulaire des critères pour l'affichage des tables
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource CriteresForm.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class CriteresForm extends AbstractSbmForm implements InputFilterProviderInterface
{

    private $tableName;

    public function __construct($tableName = null)
    {
        $this->tableName = $tableName;
        parent::__construct('criteres');
        $this->setAttribute('method', 'post');
        
        $method = 'form' . ucwords(strtolower($tableName));
        if (method_exists($this, $method)) {
            $this->$method();
        }
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'title' => 'Rechercher',
                'id' => 'criteres-submit',
                'autofocus' => 'autofocus',
                'class' => 'fam-find button submit'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        $method = 'form' . ucwords(strtolower($this->tableName)) . 'Specification';
        if (method_exists($this, $method)) {
            return $this->$method();
        }
    }

    /**
     * Affecte une classe css à tous les éléments du formulaire
     *
     * @param string $css_class            
     */
    public function setCssClass($css_class)
    {
        foreach ($this->getElements() as $element) {
            $element->setAttribute('class', $css_class);
        }
    }

    /**
     * Affecte les values_options à l'élément indiqué
     *
     * @param string $element            
     * @param array $values_options            
     */
    public function setValueOptions($element, array $values_options)
    {
        $e = $this->get($element);
        $e->setValueOptions($values_options);
    }

    /**
     * Renvoie un tableau contenant les noms des champs du formulaire (sans submit)
     *
     * @return array
     */
    public function getElementNames()
    {
        $array = array();
        foreach ($this->getElements() as $element) {
            if ($element->getName() != 'submit') {
                $array[] = $element->getName();
            }
        }
        return $array;
    }

    private function formCircuits()
    {}

    private function formClasses()
    {
        $this->add(array(
            'name' => 'nom',
            'type' => 'text',
            'attributes' => array(
                'id' => 'critere-nom',
                'maxlength' => '30',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Nom',
                'label_attributes' => array(
                    'class' => 'sbm-first'
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
                'id' => 'critere-aliasCG',
                'maxlength' => '30',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Nom CG',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formCommunes()
    {
        $this->add(array(
            'name' => 'departement',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-departement',
                'maxlength' => '2',
                'class' => 'sbm-text3'
            ),
            'options' => array(
                'label' => 'Département',
                'label_attributes' => array(
                    'class' => 'sbm-first'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'canton',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-canton',
                'maxlength' => '4',
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'Canton',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'codePostal',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-codePostal',
                'maxlength' => '5',
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'Code postal',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'nom',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-nom',
                'maxlength' => '45',
                'class' => 'sbm-text45'
            ),
            'options' => array(
                'label' => 'Nom',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'membre',
            'attributes' => array(
                'type' => 'checkbox',
                'useHiddenElement' => true,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Membres',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'desservie',
            'attributes' => array(
                'type' => 'checkbox',
                'useHiddenElement' => true,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Desservies',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formEleves()
    {
        $this->add(array(
            'name' => 'numero',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-nom',
                'maxlength' => '11',
                'class' => 'sbm-text11'
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
            'name' => 'nomSA',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-nom',
                'maxlength' => '45',
                'class' => 'sbm-text11'
            ),
            'options' => array(
                'label' => 'Nom',
                /*'label_attributes' => array(
                    'class' => 'sbm-first'
                ),*/
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'responsable',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-responsable',
                'maxlength' => '45',
                'class' => 'sbm-text11'
            ),
            'options' => array(
                'label' => 'Responsable',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'commune',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-commune',
                'maxlength' => '30',
                'class' => 'sbm-text15'
            ),
            'options' => array(
                'label' => 'Commune',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'station',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-station',
                'maxlength' => '30',
                'class' => 'sbm-text15'
            ),
            'options' => array(
                'label' => 'Station',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'service',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-service',
                'maxlength' => '11',
                'class' => 'sbm-text8'
            ),
            'options' => array(
                'label' => 'Service',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'tarifMontant',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-tarif',
                'maxlength' => '11',
                'class' => 'sbm-text8'
            ),
            'options' => array(
                'label' => 'Tarif',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'prelevement',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'type' => 'checkbox',
                'useHiddenElement' => true,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Prélevés',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'etablissement',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-etablissement',
                'maxlength' => '45',
                'class' => 'sbm-text20'
            ),
            'options' => array(
                'label' => 'Etablissement',
                'label_attributes' => array(
                    'class' => 'sbm-first'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'classe',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-classe',
                'maxlength' => '30',
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'Classe',
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
                'useHiddenElement' => true,
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
    }

    private function formEtablissements()
    {
        $this->add(array(
            'name' => 'commune',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-commune',
                'maxlength' => '45',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Commune',
                'label_attributes' => array(
                    'class' => 'sbm-first'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'nom',
            'attributes' => array(
                'type' => 'text',
                'maxlength' => '45',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Nom',
                'id' => 'critere-nom',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'aliasCG',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-aliasCG',
                'maxlength' => '50',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Nom CG',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formLibelles()
    {
        $this->add(array(
            'name' => 'nature',
            'attribues' => array(
                'type' => 'text',
                'id' => 'critere-nature',
                'maxlength' => '20',
                'class' => 'sbm-text20'
            ),
            'options' => array(
                'label' => 'Nature',
                'label_attributes' => array(
                    'class' => 'sbm-first'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'ouvert',
            'attributes' => array(
                'useHiddenElement' => true,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Ouvert ',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formPaiements()
    {
        $this->add(array(
            'name' => 'responsable',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-responsable',
                'class' => 'sbm-text30',
                'maxlegth' => '61'
            ),
            'options' => array(
                'label' => 'Responsable',
                'label_attributes' => array(
                    'class' => 'sbm-critere-responsable sbm-first'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'codeCaisse',
            'attributes' => array(
                'id' => 'critere-code-caisse',
                'class' => 'sbm-select1'
            ),
            'options' => array(
                'label' => 'Caisse',
                'label_attributes' => array(
                    'class' => ''
                ),
                'empty_option' => 'Toutes',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'codeModeDePaiement',
            'attributes' => array(
                'id' => 'critere-code-mode-de-paiement',
                'class' => 'sbm-select1'
            ),
            'options' => array(
                'label' => 'Mode de paiement',
                'label_attributes' => array(
                    'class' => ''
                ),
                'empty_option' => 'Toutes',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'exercice',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-exercice',
                'class' => 'sbm-text4',
                'maxlegth' => '4'
            ),
            'options' => array(
                'label' => 'Exercice',
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'anneeScolaire',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-annee-scolaire',
                'class' => 'sbm-text9',
                'maxlegth' => '9'
            ),
            'options' => array(
                'label' => 'Année scolaire',
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Date',
            'name' => 'dateValeur',
            'attributes' => array(
                'id' => 'critere-date-valeur',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Date de valeur',
                'label_attributes' => array(
                    'class' => 'sbm-critere-date-valeur sbm-first sbm-new-line'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Date',
            'name' => 'datePaiement',
            'attributes' => array(
                'id' => 'critere-date-paiement',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Date de paiement',
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Date',
            'name' => 'dateDepot',
            'attributes' => array(
                'id' => 'critere-date-depot',
                'class' => ''
            ),
            'options' => array(
                'label' => 'Date de dépôt',
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'titulaire',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-titulaire',
                'class' => 'sbm-text30',
                'maxlegth' => '30'
            ),
            'options' => array(
                'label' => 'Titulaire',
                'label_attributes' => array(
                    'class' => 'sbm-critere-titulaire sbm-first sbm-new-line'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'banque',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-banque',
                'class' => 'sbm-text30',
                'maxlegth' => '30'
            ),
            'options' => array(
                'label' => 'Banque',
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'reference',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-reference',
                'class' => 'sbm-text30',
                'maxlegth' => '30'
            ),
            'options' => array(
                'label' => 'Référence',
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formPaiementsSpecification()
    {
        return array(
            'codeCaisse' => array(
                'required' => false
            ),
            'codeModeDePaiement' => array(
                'required' => false
            ),
            'dateDepot' => array(
                'required' => false
            ),
            'datePaiement' => array(
                'required' => false
            ),
            'dateValeur' => array(
                'required' => false
            )
        );
    }

    private function formResponsables()
    {
        $this->add(array(
            'name' => 'nomSA',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-nom',
                'maxlength' => '45',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Nom',
                'label_attributes' => array(
                    'class' => 'sbm-first'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'commune',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-commune',
                'maxlength' => '45',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Commune',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'selection',
            'attributes' => array(
                
                // 'type' => 'checkbox',
                'useHiddenElement' => true,
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
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'demenagement',
            'attributes' => array(
                
                // 'type' => 'checkbox',
                'useHiddenElement' => true,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Déménagement',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'nbEleves',
            'attributes' => array(
                'id' => 'critere-nbEleves',
                'maxlength' => 2,
                'class' => 'sbm-text3'
            ),
            'options' => array(
                'label' => 'Nb d\'élèves',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formServices()
    {
        $this->add(array(
            'name' => 'serviceId',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-serviceId',
                'maxlength' => '11',
                'class' => 'sbm-text11'
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
            'name' => 'nom',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-nom',
                'maxlength' => '45',
                'class' => 'sbm-text45'
            ),
            'options' => array(
                'label' => 'Nom',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'transporteur',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-transporteur',
                'maxlength' => '30',
                'class' => 'sbmtext30'
            ),
            'options' => array(
                'label' => 'Transporteur',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formStations()
    {
        $this->add(array(
            'name' => 'commune',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-commune',
                'maxlength' => '45',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Commune',
                'label_attributes' => array(
                    'class' => 'sbm-first'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'nom',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-nom',
                'maxlength' => '45',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Nom',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'aliasCG',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-aliasCG',
                'maxlength' => '45',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Nom CG',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formTarifs()
    {
        $this->add(array(
            'name' => 'montant',
            'type' => 'text',
            'attributes' => array(
                'id' => 'critere-montant',
                'maxlength' => '11',
                'class' => 'sbm-text11'
            ),
            'options' => array(
                'label' => 'Montant',
                'label_attributes' => array(
                    'class' => 'sbm-first'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'rythme',
            'attributes' => array(
                'id' => 'critere-rytme',
                'class' => 'sbm-select2'
            ),
            'options' => array(
                'label' => 'Rythme de paiement',
                'empty_option' => 'Tous',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'grille',
            'attributes' => array(
                'id' => 'critere-grille',
                'class' => 'sbm-select2'
            ),
            'options' => array(
                'label' => 'Grille tarifaire',
                'empty_option' => 'Toutes',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'mode',
            'attributes' => array(
                'id' => 'critere-mode',
                'class' => 'sbm-select2'
            ),
            'options' => array(
                'label' => 'Mode de paiement',
                'empty_option' => 'Tous',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }
    private function formTarifSpecification()
    {
        return array(
            'rythme' => array(
                'required' => false
            ),
            'grille' => array(
                'required' => false
            ),
            'mode' => array(
                'required' => false
            ),
        );
    }

    private function formTransporteurs()
    {
        $this->add(array(
            'name' => 'commune',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-commune',
                'maxlength' => '45',
                'class' => 'sbm-text45'
            ),
            'options' => array(
                'label' => 'Commune',
                'label_attributes' => array(
                    'class' => 'sbm-first'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'nom',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-nom',
                'maxlength' => '30',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Nom',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }
}