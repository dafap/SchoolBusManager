<?php
/**
 * Formulaire de saisie et modificationd d'une transporteur
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Transporteur.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Form;

class Transporteur extends AbstractSbmForm
{

    public function __construct($param = 'transporteur')
    {
        parent::__construct($param);
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
                'class' => 'sbm-text30'
            ),
            'options' => array(
                'label' => 'Libellé du transporteur',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'id' => 'transporteur-aliascg',
                'class' => 'sbm-text38'
            ),
            'options' => array(
                'label' => 'Adresse',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'id' => 'transporteur-aliascg',
                'class' => 'sbm-text38'
            ),
            'options' => array(
                'label' => 'Adresse (suite)',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'Code postal',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Commune',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'class' => 'sbm-text10'
            ),
            'options' => array(
                'label' => 'Téléphone',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'class' => 'sbm-text10'
            ),
            'options' => array(
                'label' => 'Fax',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'class' => 'sbm-text80'
            ),
            'options' => array(
                'label' => 'Email',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'class' => 'sbm-text14'
            ),
            'options' => array(
                'label' => 'SIRET',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'class' => 'sbm-text5'
            ),
            'options' => array(
                'label' => 'NAF',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'class' => 'sbm-text32'
            ),
            'options' => array(
                'label' => 'RIB - titulaire',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'class' => 'sbm-text24'
            ),
            'options' => array(
                'label' => 'RIB - domiciliation',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'class' => 'sbm-text11'
            ),
            'options' => array(
                'label' => 'RIB - BIC',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'class' => 'sbm-text34'
            ),
            'options' => array(
                'label' => 'RIB - IBAN',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Commune',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'empty_option' => 'Choisissez une commune',
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
                'autofocus' => 'autofocus',
                'class' => 'button submit left135'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'transporteur-cancel',
                'class' => 'button cancel'
            )
        ));
    }
}