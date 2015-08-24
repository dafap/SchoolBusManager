<?php
/**
 * Formulaire de saisie et modification d'un lien etablissement-service
 * 
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource EtablissementService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 mars 2015
 * @version 2015-1
 */
namespace SbmCommun\Form;

class EtablissementService extends AbstractSbmForm
{

    /**
     * Désigne la colonne qui sera dans un Select.
     * L'autre sera dans un hidden.
     *
     * @var string
     */
    private $select;

    public function __construct($select = 'service', $param = 'etablissement')
    {
        $this->select = $select;
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
            'name' => 'origine',
            'type' => 'hidden'
        ));
        if ($select == 'service') {
            $this->add(array(
                'name' => 'etablissementId',
                'type' => 'hidden',
                'attributes' => array(
                    'id' => 'etablissementIdElement'
                )    
            ));
            $this->add(array(
                'name' => 'serviceId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => array(
                    'id' => 'serviceIdElement',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-45c'
                ),
                'options' => array(
                    'label' => 'Service',
                    'label_attributes' => array(
                        'class' => 'sbm-label'
                    ),
                    'empty_option' => 'Choisissez un service',
                    'error_attributes' => array(
                        'class' => 'sbm-error'
                    )
                )
            ));
        } else {
            $this->add(array(
                'name' => 'serviceId',
                'type' => 'hidden',
                'attributes' => array(
                    'id' => 'serviceIdElement'
                )
            ));
            $this->add(array(
                'name' => 'etablissementId',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => array(
                    'id' => 'etablissementIdElement',
                    'autofocus' => 'autofocus',
                    'class' => 'sbm-width-45c'
                ),
                'options' => array(
                    'label' => 'Etablissement',
                    'label_attributes' => array(
                        'class' => 'sbm-label'
                    ),
                    'empty_option' => 'Choisissez un établissement',
                    'error_attributes' => array(
                        'class' => 'sbm-error'
                    )
                )
            ));
        }
        $this->add(array(
            'name' => 'stationId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'stationIdElement',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Station desservant l\'établissement',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Choisissez une station',
                'disable_inarray_validator' => true,
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
}