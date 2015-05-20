<?php
/**
 * Formulaire de saisie et modification d'une station
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Station.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Station extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct($param = 'station')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'stationId',
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
            'name' => 'communeId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'station-communeId',
                'autofocus' => 'autofocus',
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
            'name' => 'nom',
            'type' => 'text',
            'attributes' => array(
                'id' => 'station-nom',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Nom de la station',
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
                'id' => 'station-aliasCG',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Nom CG',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'codeCG',
            'type' => 'text',
            'attributes' => array(
                'id' => 'station-codeCG',
                'class' => 'sbm-width-15c'
            ),
            'options' => array(
                'label' => 'Code CG',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'x',
            'type' => 'text',
            'attributes' => array(
                'id' => 'station-x',
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
                'id' => 'station-y',
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
            'name' => 'visible',
            'attributes' => array(
                'id' => 'station-visible',
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
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'ouverte',
            'attributes' => array(
                'id' => 'station-ouverte',
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Ouverte',
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

    public function getInputFilterSpecification()
    {
        return array(
            'communeId' => array(
                'name' => 'communeId',
                'required' => true
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
                ),
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
                ),
            ),
            'codeCG' => array(
                'name' => 'codeCG',
                'required' => false, 
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    )
                ),
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
}