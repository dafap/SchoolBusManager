<?php
/**
 * Formulaire de saisie et modification d'une service
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Service.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Form;

class Service extends AbstractSbmForm
{

    public function __construct($param = 'service')
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
            'name' => 'serviceId',
            'type' => 'text',
            'attributes' => array(
                'id' => 'service-codeid',
                'class' => 'sbm-text11'
            ),
            'options' => array(
                'label' => 'Code du service',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'id' => 'service-nom',
                'class' => 'sbm-text45'
            ),
            'options' => array(
                'label' => 'Désignation du service',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'id' => 'service-aliascg',
                'class' => 'sbm-text15'
            ),
            'options' => array(
                'label' => 'Désignation au CG',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'transporteurId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'service-transporteurId',
                'class' => 'sbm-select4'
            ),
            'options' => array(
                'label' => 'Transporteur',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'nbPlaces',
            'type' => 'text',
            'attributes' => array(
                'id' => 'service-nbPlaces',
                'class' => 'sbm-text3'
            ),
            'options' => array(
                'label' => 'Nombre de places',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'surEtatCG',
            'attributes' => array(
                'id' => 'service-surEtatCG',
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Sur les états du CG',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
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
                'id' => 'service-submit',
                'autofocus' => 'autofocus',
                'class' => 'button submit left135'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'service-cancel',
                'class' => 'button cancel'
            )
        ));
    }

    public function modifFormForEdit()
    {
        $e = $this->remove('serviceId');
        $this->add(array(
            'name' => 'serviceId',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'codeService',
            'type' => 'text',
            'attributes' => array(
                'id' => 'service-codeid',
                'disabled' => 'disabled',
                'class' => 'sbm-text11'
            ),
            'options' => array(
                'label' => 'Code du service',
                'label_attributes' => array(
                    'class' => 'sbm-label130'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
    }

    public function setData($data)
    {
        parent::setData($data);
        if ($this->has('codeService')) {
            $e = $this->get('codeService');
            $e->setValue($this->get('serviceId')
                ->getValue());
        }
    }
}