<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource AffectationDecision.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 avr. 2015
 * @version 2015-1
 */
namespace SbmGestion\Form;

use SbmCommun\Form\AbstractSbmForm As Form;
use Zend\InputFilter\InputFilterProviderInterface;

class AffectationDecision extends Form implements InputFilterProviderInterface
{

    /**
     * Correspond au n° de trajet.
     * Prend la valeur 1 ou 2 selon qu'il s'agit du trajet 1 ou 2
     *
     * @var int
     */
    private $trajet;

    /**
     * Correspond au n° de la phase d'inscription
     *
     * @var int
     */
    private $phase;

    /**
     * Constructeur du formulaire
     *
     * @param int $trajet
     *            Correspond au n° de trajet. Prend la valeur 1 ou 2 selon qu'il s'agit du trajet 1 ou 2
     * @param int $phase
     *            Correspond au n° de phase. Prend la valeur 1 ou 2 selon qu'il s'agit de la phase 1 ou 2 de l'affectation
     */
    public function __construct($trajet, $phase)
    {
        $this->trajet = $trajet;
        $this->phase = $phase;
        parent::__construct('decision');
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
        // les hiddens reçus en post et à transmettre à nouveau
        foreach (array(
            'eleveId',
            'millesime',
            'trajet',
            'jours',
            'sens',
            'correspondance',
            'responsableId',
            'demandeR' . $trajet,
            'op'
        ) as $name) {
            $this->add(array(
                'name' => $name,
                'type' => 'hidden'
            ));
        }
        
        if ($phase == 1) {
            $this->preparePhase1();
        } else {
            $this->preparePhase2();
            $this->add(array(
                'name' => 'back',
                'attributes' => array(
                    'type' => 'submit',
                    'value' => 'Précédent',
                    'id' => 'decision-cancel',
                    'autofocus' => 'autofocus',
                    'class' => 'button default cancel'
                )
            ));
        }
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'decision-cancel',
                'autofocus' => 'autofocus',
                'class' => 'button default cancel'
            )
        ));        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Valider',
                'id' => 'decision-submit',
                'class' => 'button default submit'
            )
        ));
    }

    /**
     * Crée les éléments du formulaire pour la phase 1
     */
    private function preparePhase1()
    {
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'fictif',
            'attributes' => array(
                'id' => 'decision_district',
                'class' => 'sbm-checkbox',
                'disabled' => 'disabled'
            ),
            'options' => array(
                'label' => 'Secteur scolaire',
                'label_attributes' => array(
                    'class' => 'sbm-label sbm-form-auto'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'derogation',
            'attributes' => array(
                'id' => 'decision_derogation',
                'class' => 'sbm-checkbox',
                'onclick' => 'adaptedecision();'
            ),
            'options' => array(
                'label' => 'Dérogation',
                'label_attributes' => array(
                    'class' => 'sbm-label sbm-form-auto'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        
        $this->add(array(
            'type' => 'text',
            'name' => 'motifDerogation' . $this->trajet,
            'attributes' => array(
                'id' => 'decision_motifDerogation',
                'class' => 'sbm-width-35c'
            ),
            'options' => array(
                'label' => 'Motif de la dérogation',
                'label_attributes' => array(
                    'class' => 'sbm-label sbm-form-auto'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error sbm-form-auto'
                )
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'accordR' . $this->trajet,
            'attributes' => array(
                'id' => 'decision_accordR',
                'class' => 'sbm-checkbox',
                'onclick' => 'adaptedecision();'
            ),
            'options' => array(
                'label' => 'Transport accepté',
                'label_attributes' => array(
                    'class' => 'sbm-label sbm-form-auto'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        
        $this->add(array(
            'type' => 'text',
            'name' => 'motifRefusR' . $this->trajet,
            'attributes' => array(
                'id' => 'decision_motifRefusR',
                'class' => 'sbm-width-35c'
            ),
            'options' => array(
                'label' => 'Motif du refus',
                'label_attributes' => array(
                    'class' => 'sbm-label sbm-form-auto'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error sbm-form-auto'
                )
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'subventionR' . $this->trajet,
            'attributes' => array(
                'id' => 'decision_subventionR',
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Subvention attribuée',
                'label_attributes' => array(
                    'class' => 'sbm-label sbm-form-auto'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }
    
    /**
     * Crée les éléments du formulaire pour la phase 2
     */
    private function preparePhase2()
    {
        $this->add(array(
            'name' => 'service1Id',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'affectation-service1Id',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Circuit',
                'label_attributes' => array(
                    'class' => 'sbm-form-auto'
                ),
                'empty_option' => 'Choisissez un circuit',
                //'allow_empty' => true,
                //'disable_inarray_validator' => false,
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));        
        $this->add(array(
            'name' => 'station1Id',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'affectation-station1Id',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Point de montée',
                'label_attributes' => array(
                    'class' => 'sbm-form-auto'
                ),
                'empty_option' => 'Choisissez une station',
                //'allow_empty' => true,
                //'disable_inarray_validator' => false,
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'station2Id',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'affectation-station2Id',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Point de descente',
                'label_attributes' => array(
                    'class' => 'sbm-form-auto'
                ),
                'empty_option' => 'Choisissez une station',
                'allow_empty' => true,
                'disable_inarray_validator' => false,
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'service2Id',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'affectation-service2Id',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Correspondance',
                'label_attributes' => array(
                    'class' => 'sbm-form-auto'
                ),
                'empty_option' => 'Choisissez un circuit',
                'allow_empty' => true,
                'disable_inarray_validator' => false,
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }
    
    /**
     * En phase 1, pour afficher un checkbox disabled avec la valeur de district, car l'élément district ne fait pas partie du formulaire
     * Dans tous les cas, la sortie du formulaire doit se faire avec un demandeR1 ou un demandeR2 (selon trajet) égal à 2 (demandé et traité)
     * 
     * (non-PHPdoc)
     * @see \Zend\Form\Form::setData()
     */
    public function setData($data)
    {
        if ($this->phase == 1 && array_key_exists('district', $data)) {
            $fictif = $this->get('fictif');
            $fictif->setValue($data['district']);
        }
        parent::setData($data);
        $demande = $this->get('demandeR' . $this->trajet);
        $demande->setValue(2);
        return $this;
    }

    public function getInputFilterSpecification()
    {
        if ($this->phase == 1) {
            return array(
                'fictif' => array(
                    'required' => false
                ),
                'derogation' => array(
                    'required' => true
                ),
                'accordR' . $this->trajet => array(
                    'required' => true
                ),
                'subventionR' . $this->trajet => array(
                    'required' => true
                ),
                'motifDerogation' . $this->trajet => array(
                    'required' => false,
                    'filters' => array(
                        array(
                            'name' => 'StripTags'
                        ),
                        array(
                            'name' => 'StringTrim'
                        )
                    )
                ),
                'motifRefusR' . $this->trajet => array(
                    'required' => false,
                    'filters' => array(
                        array(
                            'name' => 'StripTags'
                        ),
                        array(
                            'name' => 'StringTrim'
                        )
                    )
                )
            );
        } else {
            return array(
                'station2Id' => array(
                    'required' => false
                ),
                'service2Id' => array(
                    'required' => false
                )
            );
        }
    }
}