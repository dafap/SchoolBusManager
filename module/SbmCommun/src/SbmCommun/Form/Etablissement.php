<?php
/**
 * Formulaire de saisie et modification d'un etablissement
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Etablissement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Form;

class Etablissement extends AbstractSbmForm
{

    public function __construct($param = 'etablissement')
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
            'name' => 'etablissementId',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-codeid',
                'class' => 'sbm-text8'
            ),
            'options' => array(
                'label' => 'Code de l\'établissement',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'nom',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-nom',
                'class' => 'sbm-text45'
            ),
            'options' => array(
                'label' => 'Nom de l\'établissement',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'alias',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-alias',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Autre désignation',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
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
                'id' => 'etablissement-aliasCG',
                'class' => 'sbm-text50'
            ),
            'options' => array(
                'label' => 'Désignation au CG',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'adresse1',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-adresse1',
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
            'name' => 'adresse2',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-adresse2',
                'class' => 'sbm-text38'
            ),
            'options' => array(
                'label' => 'Adresse (suite)',
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
                'id' => 'etablissement-codepostal',
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
                'id' => 'etablissement-communeId',
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
            'name' => 'directeur',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-directeur',
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Nom du directeur',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'telephone',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-telephone',
                'class' => 'sbm-text10'
            ),
            'options' => array(
                'label' => 'Téléphone',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'fax',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-fax',
                'class' => 'sbm-text10'
            ),
            'options' => array(
                'label' => 'Fax',
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
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-email',
                'class' => 'sbm-text80'
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
            'name' => 'niveau',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'etablissement-niveau',
                'class' => 'sbm-select2'
            ),
            'options' => array(
                'label' => 'Niveau',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez un niveau',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'statut',
            'attributes' => array(
                'id' => 'etablissement-statut',
                'class' => 'sbm-radio'
            ),
            'options' => array(
                'label' => 'Statut',
                'label_attributes' => array(
                    'class' => 'sbm-label-radio'
                ),
                'value_options' => array(
                    '0' => 'Privé',
                    '1' => 'Public'
                )
            )
        ));
        $this->add(array(
            'name' => 'hMatin',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-hMatin',
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'Matin : heure d\'entrée',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'hMidi',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-hMidi',
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'Midi : heure de sortie',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'hAMidi',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-hAMidi',
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'Après-midi : heure d\'entrée',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'hSoir',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-hSoir',
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'Après-midi : heure de sortie',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'name' => 'jOuverture',
            'attributes' => array(
                'id' => 'etablissement-jOuverture',
                'class' => 'sbm-multicheckbox'
            ),
            'options' => array(
                'label' => 'Cochez les jours d\'ouverture',
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
            'name' => 'regrPeda',
            'attributes' => array(
                'id' => 'etablissement-regrPeda',
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Regroupement pédagogique',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'rattacheA',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'etablissement-rattacheA',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Secteur scolaire',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'empty_option' => 'Choisissez un établissement',
                'allow_empty' => true,
                'disable_inarray_validator' => false,
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'latitude',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-latitude',
                'class' => 'sbm-text20'
            ),
            'options' => array(
                'label' => 'Latitude',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'longitude',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-longitude',
                'class' => 'sbm-text20'
            ),
            'options' => array(
                'label' => 'Longitude',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
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
                'id' => 'etablissement-visible',
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Etablissement visible',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
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
                'value' => 'Enregistrer',
                'id' => 'etablissement-submit',
                'autofocus' => 'autofocus',
                'class' => 'button submit left135'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'etablissement-cancel',
                'class' => 'button cancel'
            )
        ));
        $this->getInputFilter()->get('rattacheA')->setRequired(false);
    }

    public function modifFormForEdit()
    {
        $e = $this->remove('etablissementId');
        $this->add(array(
            'name' => 'etablissementId',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'codeEtablissement',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-codeid',
                'disabled' => 'disabled',
                'class' => 'sbm-text8'
            ),
            'options' => array(
                'label' => 'Code de l\'établissement',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'form etablissement error error-codeid'
                )
            )
        ));
    }

    public function setData($data)
    {
        parent::setData($data);
        if ($this->has('codeEtablissement')) {
            $e = $this->get('codeEtablissement');
            $e->setValue($this->get('etablissementId')
                ->getValue());
        }
    }
}