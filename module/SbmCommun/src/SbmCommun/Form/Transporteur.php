<?php
/**
 * Formulaire de saisie et modification d'un transporteur
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Transporteur.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2016
 * @version 2016-2
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Transporteur extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('transporteur');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'transporteurId',
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
            'name' => 'nom',
            'type' => 'text',
            'attributes' => array(
                'id' => 'transporteur-nom',
                'autofocus' => 'autofocus',
                'class' => 'sbm-width-50c'
            ),
            'options' => array(
                'label' => 'Nom du transporteur',
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
            'type' => 'text',
            'attributes' => array(
                'id' => 'transporteur-adresseL1',
                'class' => 'sbm-width-50c'
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
            'type' => 'text',
            'attributes' => array(
                'id' => 'transporteur-adresseL2',
                'class' => 'sbm-width-50c'
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
                'id' => 'transporteur-codepostal',
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
                'id' => 'transporteur-communeId',
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
            'name' => 'telephone',
            'type' => 'text',
            'attributes' => array(
                'id' => 'transporteur-telephone',
                'class' => 'sbm-width-15c'
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
            'type' => 'text',
            'attributes' => array(
                'id' => 'transporteur-fax',
                'class' => 'sbm-width-15c'
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
            'type' => 'text',
            'attributes' => array(
                'id' => 'transporteur-email',
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
            'name' => 'siret',
            'type' => 'text',
            'attributes' => array(
                'id' => 'transporteur-siret',
                'class' => 'sbm-width-15c'
            ),
            'options' => array(
                'label' => 'SIRET',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'naf',
            'type' => 'text',
            'attributes' => array(
                'id' => 'transporteur-naf',
                'class' => 'sbm-width-5c'
            ),
            'options' => array(
                'label' => 'NAF',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'tvaIntraCommunautaire',
            'type' => 'text',
            'attributes' => array(
                'id' => 'transporteur-tvaIntraCommunautaire',
                'class' => 'sbm-width-15c'
            ),
            'options' => array(
                'label' => 'TVA intra communautaire',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'rib_titulaire',
            'type' => 'text',
            'attributes' => array(
                'id' => 'transporteur-rib_titulaire',
                'class' => 'sbm-width-35c'
            ),
            'options' => array(
                'label' => 'RIB - titulaire',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'rib_domiciliation',
            'type' => 'text',
            'attributes' => array(
                'id' => 'transporteur-rib_domiciliation',
                'class' => 'sbm-width-35c'
            ),
            'options' => array(
                'label' => 'RIB - domiciliation',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'rib_bic',
            'type' => 'text',
            'attributes' => array(
                'id' => 'transporteur-rib_bic',
                'class' => 'sbm-width-15c'
            ),
            'options' => array(
                'label' => 'RIB - BIC',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'rib_iban',
            'type' => 'text',
            'attributes' => array(
                'id' => 'transporteur-rib_iban',
                'class' => 'sbm-width-35c'
            ),
            'options' => array(
                'label' => 'RIB - IBAN',
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
                'id' => 'transporteur-submit',
                'class' => 'button default submit'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'transporteur-cancel',
                'class' => 'button default cancel'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'nom' => array(
                'name' => 'nom',
                'required' => true
            ),
            'codePostal' => array(
                'name' => 'codePostal',
                'required' => true
            ),
            'communeId' => array(
                'name' => 'communeId',
                'required' => true
            ),
             'telephone' => array(
                'name' => 'telephone',
                'required' => true
            ),
            'email' => array(
                'name' => 'email',
                'required' => true
            )
        );
    }
}