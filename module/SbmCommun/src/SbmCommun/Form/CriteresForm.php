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

use Zend\Form\Form;

class CriteresForm extends Form
{

    public function __construct($tableName = null)
    {
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
                'value' => 'Rechercher',
                'id' => 'criteres-submit',
                'autofocus' => 'autofocus',
                'class' => 'button submit'
            )
        ));
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
            'name' => 'nomSA',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-nom',
                'maxlength' => '45',
                'class' => 'sbm-text45'
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
            'name' => 'commune1',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-commune',
                'maxlength' => '45',
                'class' => 'sbm-text45'
            ),
            'options' => array(
                'label' => 'Commune',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'station1',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-station',
                'maxlength' => '30',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Station',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'service1',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-service',
                'maxlength' => '11',
                'class' => 'sbm-text11'
            ),
            'options' => array(
                'label' => 'Service',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'etablissement',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-etablissemeny=t',
                'maxlength' => '45',
                'class' => 'sbm-text45'
            ),
            'options' => array(
                'label' => 'Etablissement',
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
                'maxlength' => '20',
                'class' => 'sbm-text20'
            ),
            'options' => array(
                'label' => 'Tarif',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'secondeAdresse',
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
                'label' => '2nd adresse',
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
        $this->getInputFilter()
            ->get('rythme')
            ->setRequired(false);
        $this->getInputFilter()
            ->get('grille')
            ->setRequired(false);
        $this->getInputFilter()
            ->get('mode')
            ->setRequired(false);
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