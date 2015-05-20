<?php
/**
 * Formulaire de création d'un élève (phase 2)
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
 * @filesource AddElevePhase2.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 mai 2015
 * @version 2015-1
 */
namespace SbmGestion\Form\Eleve;

use Zend\InputFilter\InputFilterProviderInterface;
use SbmCommun\Form\AbstractSbmForm;

class AddElevePhase2 extends AbstractSbmForm  implements InputFilterProviderInterface
{

    public function __construct($options = array())
    {
        parent::__construct('eleve', $options);
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
            'name' => 'responsable1Id',
            'type' => 'hidden',
            'attributes' => array(
                'id' => 'eleve-responsable1Id'
            )
        ));
        $this->add(array(
            'name' => 'responsable2Id',
            'type' => 'hidden',
            'attributes' => array(
                'id' => 'eleve-responsable2Id'
            )
        ));
        // saisies de la phase 2
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'anneeComplete',
            'attributes' => array(
                'id' => 'eleve-anneeComplete',
                'value' => '1'
            ),
            'options' => array(
                'label' => 'Année complète',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0'
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Date',
            'name' => 'dateDebut',
            'attributes' => array(
                'id' => 'eleve-dateDebut',
                'class' => 'sbm-width-15c'
            ),
            'options' => array(
                'label' => 'Début',
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
            'type' => 'Zend\Form\Element\Date',
            'name' => 'dateFin',
            'attributes' => array(
                'id' => 'eleve-dateFin',
                'class' => 'sbm-width-15c'
            ),
            'options' => array(
                'label' => 'Fin',
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
            'type' => 'Zend\Form\Element\Select',
            'name' => 'etablissementId',
            'attributes' => array(
                'id' => 'eleve-etablissementId',
                'class' => 'sbm-width-40c'
            ),
            'options' => array(
                'label' => 'Etablissement',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Choisissez un établissement scolaire',
                'error_attributes' => array(
                    'class' => 'sbm_error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'classeId',
            'attributes' => array(
                'id' => 'eleve-classeId',
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Classe',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Choisissez une classe',
                'error_attributes' => array(
                    'class' => 'sbm_error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'district',
            'attributes' => array(
                'id' => 'eleve-district',
                'value' => '1'
            ),
            'options' => array(
                'label' => 'District',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0'
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'paiement',
            'attributes' => array(
                'id' => 'eleve-paiement',
                'value' => '0'
            ),
            'options' => array(
                'label' => 'Paiement',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0'
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'derogation',
            'attributes' => array(
                'id' => 'eleve-derogation'
            ),
            'options' => array(
                'label' => 'Dérogation',
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
            'name' => 'motifDerogation',
            'attributes' => array(
                'id' => 'eleve-motifDerogation',
                'class' => 'sbm-width-40c'
            ),
            'options' => array(
                /*'label' => 'Motif',
                 'label_attributes' => array(
                     'class' => 'sbm-label'
                 ),*/
                'error_attributes' => array(
                    'class' => 'sbm_error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'name' => 'joursTransport',
            'attributes' => array(
                'id' => 'eleve_joursTransport',
                'class' => 'sbm-multicheckbox'
            ),
            'options' => array(
                'label' => 'Demande de transport',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'distanceR1',
            'type' => 'text',
            'attributes' => array(
                'id' => 'eleve-distanceR1',
                'class' => 'sbm-width-10c',
                'title' => 'Double-clic pour calculer la distance',
                'autocomplete' => 'off'
            ),
            'options' => array(
                'label' => 'Distance',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'distanceR2',
            'type' => 'text',
            'attributes' => array(
                'id' => 'eleve-distanceR2',
                'class' => 'sbm-width-10c',
                'title' => 'Double-clic pour calculer la distance',
                'autocomplete' => 'off'
            ),
            'options' => array(
                'label' => 'Distance',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'demandeR1',
            'attributes' => array(
                'id' => 'eleve-demandeR1',
                'class' => 'sbm-radio ouinon'
            ),
            'options' => array(
                'label' => 'Demande',
                'label_attributes' => array(
                    'class' => 'sbm-label-radio'
                ),
                'value_options' => array(
                    array(
                        'value' => '0',
                        'label' => 'Non',
                        'attributes' => array(
                            'id' => 'demander1radio0'
                        )
                    ),
                    array(
                        'value' => '1',
                        'label' => 'Oui',
                        'attributes' => array(
                            'id' => 'demander1radio1',
                            'checked' => 'checked'
                        )
                    )
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'demandeR2',
            'attributes' => array(
                'id' => 'eleve-demandeR2',
                'class' => 'sbm-radio ouinon'
            ),
            'options' => array(
                'label' => 'Demande',
                'label_attributes' => array(
                    'class' => 'sbm-label-radio'
                ),
                'value_options' => array(
                    array(
                        'value' => '0',
                        'label' => 'Non',
                        'attributes' => array(
                            'id' => 'demander2radio0',
                            'checked' => 'checked'
                        )
                    ),
                    array(
                        'value' => '1',
                        'label' => 'Oui',
                        'attributes' => array(
                            'id' => 'demander2radio1'
                        )
                    )
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'commentaire',
            'attributes' => array(
                'id' => 'eleve-commentaire'
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
                'id' => 'station-submit',
                'class' => 'button default submit'
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
            'demandeR2' => array(
                'name' => 'demandeR2',
                'required' => false
            )
        );
    }
}