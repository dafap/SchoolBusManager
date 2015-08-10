<?php
/**
 * Formulaire de modification d'un élève
 *
 * @project sbm
 * @package SbmGestion/Form/Eleve
 * @filesource EditForm.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2015
 * @version 2015-1
 */
namespace SbmGestion\Form\Eleve;

use Zend\InputFilter\InputFilterProviderInterface;
use SbmCommun\Form\AbstractSbmForm;

class EditForm extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct($options = array())
    {
        parent::__construct('eleve', $options);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'eleveId',
            'type' => 'hidden'
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
                'id' => 'eleve-dateN',
                'class' => 'sbm-text15'
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
            'type' => 'Zend\Form\Element\Select',
            'name' => 'etablissementId',
            'attributes' => array(
                'id' => 'eleve-etablissementId',
                'class' => 'sbm-width-45c'
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
                'disabled' => 'disabled'
            ),
            'options' => array(
                'label' => 'District',
                'label_attributes' => array(
                    'class' => 'sbm-label checkbox'
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
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'fa',
            'attributes' => array(
                'id' => 'eleve-fa'
            ),
            'options' => array(
                'label' => 'Famille d\'accueil',
                'label_attributes' => array(
                    'class' => 'sbm-label checkbox'
                ),
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0'
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
                    'class' => 'sbm-label checkbox'
                ),
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0'
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'anneeComplete',
            'attributes' => array(
                'id' => 'eleve-anneeComplete'
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
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'demandeR1',
            'attributes' => array(
                'id' => 'eleve-demandeR1',
                'class' => 'sbm-radio'
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
                        'label' => 'A traiter',
                        'attributes' => array(
                            'id' => 'demander1radio1'
                        )
                    ),
                    array(
                        'value' => '2',
                        'label' => 'Traitée',
                        'attributes' => array(
                            'id' => 'demander1radio2'
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
                'class' => 'sbm-radio'
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
                            'id' => 'demander2radio0'
                        )
                    ),
                    array(
                        'value' => '1',
                        'label' => 'A traiter',
                        'attributes' => array(
                            'id' => 'demander2radio1'
                        )
                    ),
                    array(
                        'value' => '2',
                        'label' => 'Traitée',
                        'attributes' => array(
                            'id' => 'demander2radio2'
                        )
                    )
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
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'accordR1',
            'attributes' => array(
                'id' => 'eleve-accordR1'
            ),
            'options' => array(
                'label' => 'Accord',
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
            'name' => 'accordR2',
            'attributes' => array(
                'id' => 'eleve-accordR2'
            ),
            'options' => array(
                'label' => 'Accord',
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
            'name' => 'subventionR1',
            'attributes' => array(
                'id' => 'eleve-subventionR1'
            ),
            'options' => array(
                'label' => 'Subvention',
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
            'name' => 'subventionR2',
            'attributes' => array(
                'id' => 'eleve-subventionR2'
            ),
            'options' => array(
                'label' => 'Subvention',
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
            'name' => 'motifRefusR1',
            'attributes' => array(
                'id' => 'eleve-motifRefusR1',
                'class' => 'sbm-width-35c'
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
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'motifRefusR2',
            'attributes' => array(
                'id' => 'eleve-motifRefusR2',
                'class' => 'sbm-width-35c'
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
            'district' => array(
                'name' => 'district',
                'required' => false
            ),
            'responsable2Id' => array(
                'name' => 'responsable2Id',
                'required' => false
            ),
            'demandeR2' => array(
                'name' => 'demandeR2',
                'required' => false
            )
        );
    }

    public function setData($data)
    {
        parent::setData($data);
        $ga = $this->get('ga');
        $ga->setValue(! empty($data['responsable2Id']));
    }
}