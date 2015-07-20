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

    /**
     * Si tableName est un tableau, ce tableau décrit les éléménts à placer dans le formulaire
     *
     * @param string|array $tableName            
     */
    public function __construct($tableName = null)
    {
        $this->tableName = is_array($tableName) ? 'generic' : $tableName;
        parent::__construct('criteres');
        $this->setAttribute('method', 'post');
        
        if ($this->tableName == 'generic') {
            $this->addGenericElements($tableName);
        } else {
            $method = 'form' . ucwords(strtolower($tableName));
            if (method_exists($this, $method)) {
                $this->$method();
            }
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
        if ($this->tableName == 'generic') {
            return $this->genericSpecification();
        } else {
            $method = 'form' . ucwords(strtolower($this->tableName)) . 'Specification';
            if (method_exists($this, $method)) {
                return $this->$method();
            } else {
                return array();
            }
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
        return $this;
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
    {
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'serviceId',
            'attributes' => array(
                'id' => 'critere-serviceId',
                'class' => 'sbm-width-55c'
            ),
            'options' => array(
                'label' => 'Service',
                'label_attributes' => array(
                    'class' => ''
                ),
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
                'class' => 'sbm-width-55c'
            ),
            'options' => array(
                'label' => 'Station',
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
                'label_attributes' => array(
                    'class' => 'sbm-new-line'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formCircuitsSpecification()
    {
        return array(
            'serviceId' => array(
                'name' => 'serviceId',
                'required' => false
            ),
            'stationId' => array(
                'name' => 'stationId',
                'required' => false
            ),
            'selection' => array(
                'name' => 'selection',
                'required' => false
            )
        );
    }

    private function formClasses()
    {
        $this->add(array(
            'name' => 'nom',
            'type' => 'text',
            'attributes' => array(
                'id' => 'critere-nom',
                'maxlength' => '30',
                'class' => 'sbm-width-30c'
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
        
        /*
         * $this->add(array(
         * 'name' => 'aliasCG',
         * 'type' => 'hidden',
         * 'attributes' => array(
         * 'id' => 'critere-aliasCG',
         * 'maxlength' => '30',
         * 'class' => 'sbm-width-30c'
         * ),
         * 'options' => array(
         * 'label' => 'Nom CG',
         * 'error_attributes' => array(
         * 'class' => 'sbm-error'
         * )
         * )
         * ));
         */
        
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
                'label_attributes' => array(
                    'class' => ''
                ),
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
                'class' => 'sbm-width-5c'
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
                'class' => 'sbm-width-5c'
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
                'class' => 'sbm-width-5c'
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
                'label_attributes' => array(
                    'class' => 'sbm-new-line'
                ),
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
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'visible',
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
                'label' => 'Visibles',
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
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formCommunesSpecification()
    {
        return array(
            'departement' => array(
                'name' => 'departement',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'canton' => array(
                'name' => 'canton',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'codePostal' => array(
                'name' => 'codePostal',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'nom' => array(
                'name' => 'nom',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'membre' => array(
                'name' => 'membre',
                'required' => false
            ),
            'desservie' => array(
                'name' => 'desservie',
                'required' => false
            ),
            'visible' => array(
                'name' => 'visible',
                'required' => false
            ),
            'selection' => array(
                'name' => 'selection',
                'required' => false
            )
        );
    }

    private function formEleves()
    {
        $this->add(array(
            'name' => 'numero',
            'attributes' => array(
                'type' => 'text',
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
            'name' => 'nomSA',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-nom',
                'maxlength' => '45',
                'class' => 'sbm-width-45c'
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
            'name' => 'prenomSA',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-prenom',
                'maxlength' => '45',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Préom',
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
            'name' => 'commune',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-commune',
                'maxlength' => '30',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Commune',
                'label_attributes' => array(
                    'class' => 'sbm-new-line'
                ),
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
                'class' => 'sbm-width-30c'
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
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Service',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        /*
         * $this->add(array(
         * 'name' => 'tarifMontant',
         * 'attributes' => array(
         * 'type' => 'text',
         * 'id' => 'critere-tarif',
         * 'maxlength' => '11',
         * 'class' => 'sbm-width-10c'
         * ),
         * 'options' => array(
         * 'label' => 'Tarif',
         * 'error_attributes' => array(
         * 'class' => 'sbm-error'
         * )
         * )
         * ));
         * $this->add(array(
         * 'name' => 'prelevement',
         * 'type' => 'Zend\Form\Element\Checkbox',
         * 'attributes' => array(
         * 'type' => 'checkbox',
         * 'useHiddenElement' => true,
         * 'options' => array(
         * 'checkedValue' => false,
         * 'uncheckedValue' => true
         * ),
         * 'class' => 'sbm-checkbox'
         * ),
         * 'options' => array(
         * 'label' => 'Prélevés',
         * 'error_attributes' => array(
         * 'class' => 'sbm-error'
         * )
         * )
         * ));
         */
        $this->add(array(
            'name' => 'etablissement',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-etablissement',
                'maxlength' => '45',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Etablissement',
                'label_attributes' => array(
                    'class' => 'sbm-new-line'
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
                'class' => 'sbm-width-10c'
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

    private function formElevesSpecification()
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
            'commune' => array(
                'name' => 'commune',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'station' => array(
                'name' => 'station',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'service' => array(
                'name' => 'service',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'etablissement' => array(
                'name' => 'etablissement',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'classe' => array(
                'name' => 'classe',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'selection' => array(
                'name' => 'selection',
                'required' => false
            )
        );
    }

    private function formEtablissements()
    {
        $this->add(array(
            'type' => 'text',
            'name' => 'commune',
            'attributes' => array(
                'id' => 'critere-commune',
                'maxlength' => '45',
                'class' => 'sbm-width-45c'
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
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Nom',
                'id' => 'critere-nom',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        /*
         * $this->add(array(
         * 'name' => 'aliasCG',
         * 'attributes' => array(
         * 'type' => 'hidden',
         * 'id' => 'critere-aliasCG',
         * 'maxlength' => '50',
         * 'class' => 'sbm-width-50c'
         * ),
         * 'options' => array(
         * 'label' => 'Nom CG',
         * 'error_attributes' => array(
         * 'class' => 'sbm-error'
         * )
         * )
         * ));
         */
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'desservie',
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
                'label' => 'Desservis',
                'label_attributes' => array(
                    'class' => 'sbm-new-line'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'visible',
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
                'label' => 'Visibles',
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'localisation',
            'attributes' => array(
                'useHiddenElement' => false,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Sans localisation',
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
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formEtablissementsSpecification()
    {
        return array(
            'commune' => array(
                'name' => 'commune',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'nom' => array(
                'name' => 'nom',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'selection' => array(
                'name' => 'selection',
                'required' => false
            ),
            'commune' => array(
                'name' => 'commune',
                'required' => false
            ),
            'localisation' => array(
                'name' => 'preinscrits',
                'required' => false
            )
        );
    }

    private function formLibelles()
    {
        $this->add(array(
            'name' => 'nature',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-nature',
                'maxlength' => '20',
                'class' => 'sbm-width-20c'
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

    private function formLibellesSpecification()
    {
        return array(
            'nature' => array(
                'name' => 'nature',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'ouvert' => array(
                'name' => 'ouvert',
                'required' => false
            )
        );
    }

    private function formPaiements()
    {
        $this->add(array(
            'name' => 'responsable',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-responsable',
                'class' => 'sbm-width-50c',
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
            'type' => 'Zend\Form\Element\Select',
            'name' => 'codeCaisse',
            'attributes' => array(
                'id' => 'critere-code-caisse',
                'class' => 'sbm-select1'
            ),
            'options' => array(
                'label' => 'Caisse',
                'label_attributes' => array(
                    'class' => 'sbm-new-line'
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
                'class' => 'sbm-width-5c',
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
                'class' => 'sbm-width-10c',
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
                'class' => 'sbm-width-30c',
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
                'class' => 'sbm-width-30c',
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
                'class' => 'sbm-width-30c',
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
            'responsable' => array(
                'name' => 'responsable',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'exercice' => array(
                'name' => 'exercice',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'anneeScolaire' => array(
                'name' => 'anneeScolaire',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'titulaire' => array(
                'name' => 'titulaire',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'banque' => array(
                'name' => 'banque',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'reference' => array(
                'name' => 'reference',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'selection' => array(
                'name' => 'selection',
                'required' => false
            ),
            'codeCaisse' => array(
                'name' => 'codeCaisse',
                'required' => false
            ),
            'codeModeDePaiement' => array(
                'name' => 'codeModeDePaiement',
                'required' => false
            ),
            'dateDepot' => array(
                'name' => 'dateDepot',
                'required' => false
            ),
            'datePaiement' => array(
                'name' => 'datePaiement',
                'required' => false
            ),
            'dateValeur' => array(
                'name' => 'dateValeur',
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
                'maxlength' => '30',
                'class' => 'sbm-width-30c'
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
            'name' => 'prenomSA',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-prenom',
                'maxlength' => '30',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Prénom',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Commune',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'nbEnfants',
            'attributes' => array(
                'id' => 'critere-nbEnfants',
                'maxlength' => 2,
                'class' => 'sbm-width-5c'
            ),
            'options' => array(
                'label' => 'Nb d\'enfants',
                'label_attributes' => array(
                    'class' => 'sbm-new-line'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'nbInscrits',
            'attributes' => array(
                'id' => 'critere-nbInscrits',
                'maxlength' => 2,
                'class' => 'sbm-width-5c'
            ),
            'options' => array(
                'label' => 'Nb d\'inscrits',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'nbPreinscrits',
            'attributes' => array(
                'id' => 'critere-nbPreinscits',
                'maxlength' => 2,
                'class' => 'sbm-width-5c'
            ),
            'options' => array(
                'label' => 'Nb de préinscrits',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'demenagement',
            'attributes' => array(
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
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'inscrits',
            'attributes' => array(
                'useHiddenElement' => false,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Inscrits',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'preinscrits',
            'attributes' => array(
                'useHiddenElement' => false,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Préinscrits',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'localisation',
            'attributes' => array(
                'useHiddenElement' => false,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Sans localisation',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'selection',
            'attributes' => array(
                'useHiddenElement' => false,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Sélectionnés',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formResponsablesSpecification()
    {
        return array(
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
            'commune' => array(
                'name' => 'commune',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'nbEnfants' => array(
                'name' => 'nbEnfants',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'nbInscrits' => array(
                'name' => 'nbInscrits',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'nbPreinscrits' => array(
                'name' => 'nbPreinscrits',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'demenagement' => array(
                'name' => 'demenagement',
                'required' => false
            ),
            'inscrits' => array(
                'name' => 'inscrits',
                'required' => false
            ),
            'preinscrits' => array(
                'name' => 'preinscrits',
                'required' => false
            ),
            'localisation' => array(
                'name' => 'preinscrits',
                'required' => false
            ),
            'selection' => array(
                'name' => 'selection',
                'required' => false
            )
        );
    }

    private function formServices()
    {
        $this->add(array(
            'name' => 'serviceId',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-serviceId',
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
            'name' => 'nom',
            'attributes' => array(
                'type' => 'text',
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
            'type' => 'Zend\Form\Element\Select',
            'name' => 'transporteurId',
            'attributes' => array(
                'id' => 'critere-transporteurId',
                'maxlength' => '30',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Transporteur',
                'empty_option' => 'Tous',
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
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formServicesSpecification()
    {
        return array(
            'serviceId' => array(
                'name' => 'serviceId',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'nom' => array(
                'name' => 'nom',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'selection' => array(
                'name' => 'selection',
                'required' => false
            ),
            'transporteurId' => array(
                'name' => 'transporteurId',
                'required' => false
            )
        );
    }

    private function formStations()
    {
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'communeId',
            'attributes' => array(
                'id' => 'critere-communeId',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Commune',
                'label_attributes' => array(
                    'class' => 'sbm-first'
                ),
                'empty_option' => 'Tous',
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
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Nom',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        /*
         * $this->add(array(
         * 'name' => 'aliasCG',
         * 'attributes' => array(
         * 'type' => 'hidden',
         * 'id' => 'critere-aliasCG',
         * 'maxlength' => '45',
         * 'class' => 'sbm-width-25c'
         * ),
         * 'options' => array(
         * 'label' => 'Nom CG',
         * 'error_attributes' => array(
         * 'class' => 'sbm-error'
         * )
         * )
         * ));
         */
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'ouverte',
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
                'label' => 'Ouvertes',
                'label_attributes' => array(
                    'class' => 'sbm-new-line'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'visible',
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
                'label' => 'Visibles',
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'localisation',
            'attributes' => array(
                'useHiddenElement' => false,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Sans localisation',
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
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formStationsSpecification()
    {
        return array(
            'nom' => array(
                'name' => 'nom',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'ouverte' => array(
                'name' => 'ouverte',
                'required' => false
            ),
            'visible' => array(
                'name' => 'visible',
                'required' => false
            ),
            'selection' => array(
                'name' => 'selection',
                'required' => false
            ),
            'communeId' => array(
                'name' => 'communeId',
                'required' => false
            ),
            'localisation' => array(
                'name' => 'preinscrits',
                'required' => false
            )
        );
    }

    private function formTarifs()
    {
        $this->add(array(
            'name' => 'montant',
            'type' => 'text',
            'attributes' => array(
                'id' => 'critere-montant',
                'maxlength' => '11',
                'class' => 'sbm-width-10c'
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
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'selection',
            'attributes' => array(
                'useHiddenElement' => false,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Sélectionnés',
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formTarifsSpecification()
    {
        return array(
            'montant' => array(
                'name' => 'montant',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            ),
            'selection' => array(
                'name' => 'selection',
                'required' => false
            ),
            'rythme' => array(
                'name' => 'rythme',
                'required' => false
            ),
            'grille' => array(
                'name' => 'grille',
                'required' => false
            ),
            'mode' => array(
                'name' => 'mode',
                'required' => false
            )
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
                'class' => 'sbm-width-45c'
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
                'class' => 'sbm-width-30c'
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
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formUsers()
    {
        $this->add(array(
            'name' => 'nom',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-nom',
                'maxlength' => '30',
                'class' => 'sbm-width-30c'
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
            'name' => 'email',
            'attributes' => array(
                'type' => 'text',
                'id' => 'critere-nom',
                'maxlength' => '80',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Email',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'categorieId',
            'attributes' => array(
                'id' => 'critere-categorieId',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Catégorie',
                'empty_option' => 'Toutes',
                'value_options' => array(
                    '1' => 'Parent',
                    '2' => 'Transporteur',
                    '3' => 'Etablissement scolaire',
                    '253' => 'Gestionnaire',
                    '254' => 'Administrateur'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'tokenalive',
            'attributes' => array(
                'useHiddenElement' => false,
                'options' => array(
                    'checkedValue' => 1,
                    'uncheckedValue' => 0
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Mot de passe inactif',
                'label_attributes' => array(
                    'class' => 'sbm-new-line'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'confirme',
            'attributes' => array(
                'useHiddenElement' => false,
                'options' => array(
                    'checkedValue' => 0,
                    'uncheckedValue' => 1
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Pas confirmés',
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'active',
            'attributes' => array(
                'useHiddenElement' => false,
                'options' => array(
                    'checkedValue' => 0,
                    'uncheckedValue' => 1
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Inactifs',
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'selection',
            'attributes' => array(
                'useHiddenElement' => false,
                'options' => array(
                    'checkedValue' => false,
                    'uncheckedValue' => true
                ),
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Sélectionnés',
                'label_attributes' => array(
                    'class' => ''
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    private function formUsersSpecification()
    {
        return array(
            'categorieId' => array(
                'name' => 'categorieId',
                'required' => false
            ),
            'tokenalive' => array(
                'name' => 'tokenalive',
                'required' => false
            ),
            'confirme' => array(
                'name' => 'confirme',
                'required' => false
            ),
            'active' => array(
                'name' => 'active',
                'required' => false
            ),
            'selection' => array(
                'name' => 'selection',
                'required' => false
            )
        );
    }

    private function addGenericElements($elements)
    {
        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    private function genericSpecification()
    {
        $array = array();
        foreach ($this->getElementNames() as $elementName) {
            $array[$elementName] = array(
                'name' => $elementName,
                'required' => false
            );
            $element = $this->get($elementName);
            if ($element->getAttribute('type') == 'text') {
                $array[$elementName]['filters'] = array(
                    'name' => 'StringTrim'
                );
            }
        }
        return $array;
    }
}