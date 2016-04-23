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
 * @date 7 avr. 2016
 * @version 2016-2
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Service extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('service');
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
                'autofocus' => 'autofocus',
                'class' => 'sbm-width-15c'
            ),
            'options' => array(
                'label' => 'Code du service',
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
                'id' => 'service-nom',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Désignation du service',
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
                'id' => 'service-aliascg',
                'class' => 'sbm-width-15c'
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
            'name' => 'transporteurId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'service-transporteurId',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Transporteur',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Choisissez un transporteur',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'operateur',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'service-operateur',
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Opérateur',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Choisissez un opérateur',
                'value_options' => array(
                    'CCDA' => 'CCDA',
                    'CG12' => 'CG12'
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
                'class' => 'sbm-width-5c'
            ),
            'options' => array(
                'label' => 'Nombre de places',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'kmAVide',
            'type' => 'text',
            'attributes' => array(
                'id' => 'service-kmAVide',
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Km à vide',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'kmEnCharge',
            'type' => 'text',
            'attributes' => array(
                'id' => 'service-kmEnCharge',
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Km en charge',
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
            'name' => 'surEtatCG',
            'attributes' => array(
                'id' => 'service-surEtatCG',
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Sur les états du CG',
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
                'id' => 'service-submit',
                'autofocus' => 'autofocus',
                'class' => 'button default submit'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'service-cancel',
                'class' => 'button default cancel'
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
        $this->get('nom')->setAttribute('autofocus', 'autofocus');
        $this->add(array(
            'name' => 'codeService',
            'type' => 'text',
            'attributes' => array(
                'id' => 'service-codeid',
                'disabled' => 'disabled',
                'class' => 'sbm-width-15c'
            ),
            'options' => array(
                'label' => 'Code du service',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        return $this;
    }

    public function getInputFilterSpecification()
    {
        return array(
            'serviceId' => array(
                'name' => 'serviceId',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'SbmCommun\Model\Validator\CodeService'
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
                    )
                )
            ),
            'transporteurId' => array(
                'name' => 'transporteurId',
                'required' => true
            ),
            'surEtatCG' => array(
                'name' => 'surEtatCG',
                'required' => false
            ),
            'nbPlaces' => array(
                'name' => 'nbPlaces',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'Digits'
                    )
                )
            )
        );
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