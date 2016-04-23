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
 * @date 7 avr. 2016
 * @version 2016-2
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Etablissement extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('etablissement');
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
                'autofocus' => 'autofocus',
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Code',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Nom',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Autre désignation',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                'class' => 'sbm-width-50c'
            ),
            'options' => array(
                'label' => 'Désignation au CG',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'adresse1',
            'type' => 'SbmCommun\Form\Element\Adresse',
            'attributes' => array(
                'id' => 'etablissement-adresse1',
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
            'name' => 'adresse2',
            'type' => 'SbmCommun\Form\Element\Adresse',
            'attributes' => array(
                'id' => 'etablissement-adresse2',
                'class' => 'sbm-width-40c'
            ),
            'options' => array(
                'label' => 'Adresse (suite)',
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
                'id' => 'etablissement-codepostal',
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
                'id' => 'etablissement-communeId',
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
            'name' => 'directeur',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-directeur',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Nom du directeur',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'telephone',
            'type' => 'SbmCommun\Form\Element\Telephone',
            'attributes' => array(
                'id' => 'etablissement-telephone',
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Téléphone',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'fax',
            'type' => 'SbmCommun\Form\Element\Telephone',
            'attributes' => array(
                'id' => 'etablissement-fax',
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Fax',
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
                'id' => 'etablissement-email',
                'class' => 'sbm-width-45c'
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
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'name' => 'niveau',
            'attributes' => array(
                'id' => 'etablissement-niveau',
                'class' => 'sbm-multicheckbox'
            ),
            'options' => array(
                'label' => 'Niveau',
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
            'type' => 'Zend\Form\Element\Time',
            'attributes' => array(
                'id' => 'etablissement-hMatin',
                'title' => 'Format hh:mm',
                'class' => 'sbm-width-10c',
                'min' => '00:00',
                'max' => '29:59',
                'step' => '60'
            ),
            'options' => array(
                'format' => 'H:i',
                'label' => 'Entrée',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'hMidi',
            'type' => 'Zend\Form\Element\Time',
            'attributes' => array(
                'id' => 'etablissement-hMidi',
                'title' => 'Format hh:mm',
                'class' => 'sbm-width-10c',
                'min' => '00:00',
                'max' => '29:59',
                'step' => '60'
            ),
            'options' => array(
                'format' => 'H:i',
                'label' => 'Sortie',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'hAMidi',
            'type' => 'Zend\Form\Element\Time',
            'attributes' => array(
                'id' => 'etablissement-hAMidi',
                'title' => 'Format hh:mm',
                'class' => 'sbm-width-10c',
                'min' => '00:00',
                'max' => '29:59',
                'step' => '60'
            ),
            'options' => array(
                'format' => 'H:i',
                'label' => 'Entrée',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'hSoir',
            'type' => 'Zend\Form\Element\Time',
            'attributes' => array(
                'id' => 'etablissement-hSoir',
                'title' => 'Format hh:mm',
                'class' => 'sbm-width-10c',
                'min' => '00:00',
                'max' => '29:59',
                'step' => '60'
            ),
            'options' => array(
                'format' => 'H:i',
                'label' => 'Sortie',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'hGarderieOMatin',
            'type' => 'Zend\Form\Element\Time',
            'attributes' => array(
                'id' => 'etablissement-hMatin',
                'title' => 'Format hh:mm',
                'class' => 'sbm-width-10c',
                'min' => '00:00',
                'max' => '29:59',
                'step' => '60'
            ),
            'options' => array(
                'format' => 'H:i',
                'label' => 'Tous les jours',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'hGarderieFSoir',
            'type' => 'Zend\Form\Element\Time',
            'attributes' => array(
                'id' => 'etablissement-hMidi',
                'title' => 'Format hh:mm',
                'class' => 'sbm-width-10c',
                'min' => '00:00',
                'max' => '29:59',
                'step' => '60'
            ),
            'options' => array(
                'format' => 'H:i',
                'label' => 'Lu Ma Je Ve',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'hGarderieFMidi',
            'type' => 'Zend\Form\Element\Time',
            'attributes' => array(
                'id' => 'etablissement-hAMidi',
                'title' => 'Format hh:mm',
                'class' => 'sbm-width-10c',
                'min' => '00:00',
                'max' => '29:59',
                'step' => '60'
            ),
            'options' => array(
                'format' => 'H:i',
                'label' => 'Mercredi',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                'label' => 'Jours d\'ouverture',
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
                    'class' => 'sbm-label'
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
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Secteur scolaire',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
            'name' => 'x',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-x',
                'title' => 'Utilisez . comme séparateur décimal',
                'class' => 'sbm-width-20c'
            ),
            'options' => array(
                'label' => 'X',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'y',
            'type' => 'text',
            'attributes' => array(
                'id' => 'etablissement-y',
                'title' => 'Utilisez . comme séparateur décimal',
                'class' => 'sbm-width-20c'
            ),
            'options' => array(
                'label' => 'Y',
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
            'name' => 'desservie',
            'attributes' => array(
                'id' => 'etablissement-desservie',
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Desservi',
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
            'name' => 'visible',
            'attributes' => array(
                'id' => 'etablissement-visible',
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Visible',
                'label_attributes' => array(
                    'class' => 'sbm-label'
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
                'class' => 'button default submit'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'etablissement-cancel',
                'class' => 'button default cancel'
            )
        ));
        $this->getInputFilter()
            ->get('rattacheA')
            ->setRequired(false);
    }

    public function modifFormForEdit()
    {
        $e = $this->remove('etablissementId');
        $this->get('nom')->setAttribute('autofocus', 'autofocus');
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
                'label' => 'Code',
                'label_attributes' => array(
                    'class' => 'sbm-label170'
                ),
                'error_attributes' => array(
                    'class' => 'form etablissement error error-codeid'
                )
            )
        ));
        return $this;
    }

    public function getInputFilterSpecification()
    {
        return array(
            'etablissementId' => array(
                'name' => 'etablissementId',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'Alnum'
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'SbmCommun\Model\Validator\CodeEtablissement'
                    )
                )
            ),
            'nom' => array(
                'name' => 'nom',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    ),
                    array(
                        'name' => 'StringToUpper'
                    )
                )
            ),
            'alias' => array(
                'name' => 'alias',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    ),
                    array(
                        'name' => 'StringToUpper'
                    )
                )
            ),
            'aliasCG' => array(
                'name' => 'aliasCG',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    ),
                    array(
                        'name' => 'StringToUpper'
                    )
                )
            ),
            'adresse1' => array(
                'name' => 'adresse1',
                'required' => false
            ),
            'adresse2' => array(
                'name' => 'adresse2',
                'required' => false
            ),
            'codePostal' => array(
                'name' => 'codePostal',
                'required' => true
            ),
            'communeId' => array(
                'name' => 'communeId',
                'required' => true
            ),
            'niveau' => array(
                'name' => 'niveau',
                'required' => true
            ),
            'statut' => array(
                'name' => 'statut',
                'required' => true
            ),
            'visible' => array(
                'name' => 'visible',
                'required' => false
            ),
            'desservie' => array(
                'name' => 'desservie',
                'required' => false
            ),
            'regrPeda' => array(
                'name' => 'regrPeda',
                'required' => false
            ),
            'rattacheA' => array(
                'name' => 'rattacheA',
                'required' => false
            ),
            'telephone' => array(
                'name' => 'telephone',
                'required' => false
            ),
            'fax' => array(
                'name' => 'fax',
                'required' => false
            ),
            'email' => array(
                'name' => 'email',
                'required' => false
            ),
            'directeur' => array(
                'name' => 'directeur',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    ),
                    array(
                        'name' => 'StringToUpper'
                    )
                )
            ),
            'jOuverture' => array(
                'name' => 'jOuverture',
                'required' => true
            ),
            'hMatin' => array(
                'name' => 'hMatin',
                'required' => false
            ),
            'hMidi' => array(
                'name' => 'hMidi',
                'required' => false
            ),
            'hAMidi' => array(
                'name' => 'hAMidi',
                'required' => false
            ),
            'hSoir' => array(
                'name' => 'hSoir',
                'required' => false
            ),
            'hGarderieOMatin' => array(
                'name' => 'hGarderieOMatin',
                'required' => false
            ),
            'hGarderieFMidi' => array(
                'name' => 'hGarderieFMidi',
                'required' => false
            ),
            'hGarderieFSoir' => array(
                'name' => 'hGarderieFSoir',
                'required' => false
            ),
            'x' => array(
                'name' => 'x',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => array(
                            'separateur' => '.',
                            'car2sep' => ','
                        )
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'SbmCommun\Model\Validator\Decimal'
                    )
                )
            ),
            'y' => array(
                'name' => 'y',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => array(
                            'separateur' => '.',
                            'car2sep' => ','
                        )
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'SbmCommun\Model\Validator\Decimal'
                    )
                )
            )
        );
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