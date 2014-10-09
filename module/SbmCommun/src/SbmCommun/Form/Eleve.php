<?php
/**
 * Formulaire de saisie et modification d'un élève
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Eleve.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 juin 2014
 * @version 2014-1
 */
namespace SbmCommun\Form;

class Eleve extends AbstractSbmForm
{

    public function __construct($param = 'eleve')
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
            'name' => 'eleveId',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'respId1',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'respId2',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'factId',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'classeId',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'etablissementId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-etablissementId',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Etablissement',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez un établissement',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'tarifId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-tarifId',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Tarif',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez un établissement',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'stationId1',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-stationId1',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Station 1 Lu Ma Je Ve',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez une station',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'stationId1m',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-stationId1m',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Station 1 Me',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez une station',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'stationId1s',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-stationId1s',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Station 1 Sa',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez une station',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'stationId2',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-stationId1',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Station 2 Lu Ma Je Ve',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez une station',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'stationId2m',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-stationId1m',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Station 2 Me',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez une station',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'stationId2s',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-stationId2s',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Station 2 Sa',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez une station',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'serviceId1',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-serviceId1',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Service 1 Lu Ma Je Ve',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez un service',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'serviceId1m',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-serviceId1m',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Service 1 Me',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez un service',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'serviceId1s',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-serviceId1s',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Service 1 Sa',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez un service',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'serviceId2',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-serviceId1',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Service 2 Lu Ma Je Ve',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez un service',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'serviceId2m',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-serviceId1m',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Service 2 Me',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez un service',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'serviceId2s',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-serviceId2s',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Service 2 Sa',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez un service',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'nom',
            'type' => 'text',
            'attributes' => array(
                'id' => 'eleve-nom',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Nom de l\'élève',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'prenom',
            'type' => 'text',
            'attributes' => array(
                'id' => 'eleve-prenom',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Prénom de l\'élève',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'adress1L1',
            'type' => 'text',
            'attributes' => array(
                'id' => 'eleve-adress1L1',
                'class' => 'sbm-text38'
            ),
            'options' => array(
                'label' => 'Adresse 1',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'adress1L2',
            'type' => 'text',
            'attributes' => array(
                'id' => 'eleve-adress1L2',
                'class' => 'sbm-text38'
            ),
            'options' => array(
                'label' => 'Adresse 1',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'codePostal1',
            'type' => 'text',
            'attributes' => array(
                'id' => 'eleve-codePostal1',
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'Code postal 1',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'communeId1',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-communeId1',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Commune 1',
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
            'name' => 'adress2L1',
            'type' => 'text',
            'attributes' => array(
                'id' => 'eleve-adress2L1',
                'class' => 'sbm-text38'
            ),
            'options' => array(
                'label' => 'Adresse 2',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'adress2L2',
            'type' => 'text',
            'attributes' => array(
                'id' => 'eleve-adress2L2',
                'class' => 'sbm-text38'
            ),
            'options' => array(
                'label' => 'Adresse 2',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'codePostal2',
            'type' => 'text',
            'attributes' => array(
                'id' => 'eleve-codePostal2',
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'Code postal 2',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'communeId2',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-communeId2',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Commune 2',
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
            'name' => 'dateN',
            'type' => 'text',
            'attributes' => array(
                'id' => 'eleve-dateN',
                'class' => 'sbm-text10'
            ),
            'options' => array(
                'label' => 'Date de naissance',
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
            'type' => 'text',
            'attributes' => array(
                'id' => 'eleve-dateDebut',
                'class' => 'sbm-text10'
            ),
            'options' => array(
                'label' => 'Début ST',
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
            'type' => 'text',
            'attributes' => array(
                'id' => 'eleve-dateFin',
                'class' => 'sbm-text10'
            ),
            'options' => array(
                'label' => 'Fin ST',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        /*$this->add(array(
            'name' => 'regimeId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'eleve-regimeId',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Régime',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez un régime',
                'value_options' => array(
                    '0' => 'D.P. ou Ext.',
                    '1' => 'Interne'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));*/
        $this->add(array(
            'name' => 'regimeId',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'id' => 'eleve-regimeId',
                'class' => 'sbm-radio'
            ),
            'options' => array(
                'label' => 'Régime',
                'label_attributes' => array(
                    'class' => 'sbm-label-radio'
                ),
                'value_options' => array(
                    '0' => 'D.P. ou Ext.',
                    '1' => 'Interne'
                )
            )
        ));
        $this->add(array(
            'name' => 'anComplet',
            'type' => 'Zend\Form\Element\Checkbox',
            'options' => array(
                'label' => 'Année complète',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0'
            )
        ));
        $this->add(array(
            'name' => 'inscrit',
            'type' => 'Zend\Form\Element\Checkbox',
            'options' => array(
                'label' => 'Inscrit',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0'
            )
        ));
        $this->add(array(
            'name' => 'selection',
            'type' => 'Zend\Form\Element\Checkbox',
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
            'name' => 'secondeAdresse',
            'type' => 'Zend\Form\Element\Checkbox',
            'options' => array(
                'label' => 'Seconde adresse',
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
                'id' => 'eleve-submit',
                'autofocus' => 'autofocus',
                'class' => 'button submit left135'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'eleve-cancel',
                'class' => 'button cancel'
            )
        ));
    }
}