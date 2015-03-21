<?php
/**
 * Formulaire de saisie et modification d'un circuit
 *
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Circuit.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 mars 2015
 * @version 2015-1
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Circuit extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct($param = 'circuit')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'circuitId',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'millesime',
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
            'name' => 'serviceId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'circuit-serviceId',
                'autofocus' => 'autofocus',
                'class' => 'sbm-width-55c'
            ),
            'options' => array(
                'label' => 'Service',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Quel service ?',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'stationId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'circuit-stationId',
                'class' => 'sbm-width-55c'
            ),
            'options' => array(
                'label' => 'Point d\'arrêt',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Quel point d\'arrêt ?',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'semaine',
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'attributes' => array(
                'id' => 'circuit-semaine',
                'class' => 'sbm-multicheckbox'
            ),
            'options' => array(
                'label' => 'Jours de passage',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'm1',
            'type' => 'Zend\Form\Element\Time',
            'attributes' => array(
                'id' => 'circuit-m1',
                'title' => 'Lundi, mardi, jeudi, vendredi. Format hh:mm',
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
            'name' => 's1',
            'type' => 'Zend\Form\Element\Time',
            'attributes' => array(
                'id' => 'circuit-s1',
                'title' => 'Lundi, mardi, jeudi, vendredi. Format hh:mm',
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
            'name' => 'm2',
            'type' => 'Zend\Form\Element\Time',
            'attributes' => array(
                'id' => 'circuit-m2',
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
            'name' => 's2',
            'type' => 'Zend\Form\Element\Time',
            'attributes' => array(
                'id' => 'circuit-s2',
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
            'name' => 'm3',
            'type' => 'Zend\Form\Element\Time',
            'attributes' => array(
                'id' => 'circuit-m3',
                'title' => 'Format hh:mm',
                'class' => 'sbm-width-10c',
                'min' => '00:00',
                'max' => '29:59',
                'step' => '60'
            ),
            'options' => array(
                'format' => 'H:i',
                'label' => 'Samedi',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 's3',
            'type' => 'Zend\Form\Element\Time',
            'attributes' => array(
                'id' => 'circuit-s3',
                'title' => 'Format hh:mm',
                'class' => 'sbm-width-10c',
                'min' => '00:00',
                'max' => '29:59',
                'step' => '60'
            ),
            'options' => array(
                'format' => 'H:i',
                'label' => 'Samedi',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'distance',
            'type' => 'text',
            'attributes' => array(
                'id' => 'circuit-distance',
                'class' => 'sbm-width-10c'
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
            'name' => 'montee',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'id' => 'circuit-montee',
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Point de montée',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'descente',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'id' => 'circuit-descente',
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Point de descente',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'typeArret',
            'type' => 'text',
            'attributes' => array(
                'id' => 'circuit-typeArret',
                'class' => 'sbm-width-55c'
            ),
            'options' => array(
                'label' => 'Type d\'arrêt',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'commentaire1',
            'type' => 'textarea',
            'attributes' => array(
                'id' => 'circuit-commentaire1',
                'class' => 'sbm-width-40c'
            ),
            'options' => array(
                'label' => 'Commentaire aller',
                'label_attributes' => array(
                    'class' => 'sbm-label-top'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'commentaire2',
            'type' => 'textarea',
            'attributes' => array(
                'id' => 'circuit-commentaire2',
                'class' => 'sbm-width-40c'
            ),
            'options' => array(
                'label' => 'Commentaire retour',
                'label_attributes' => array(
                    'class' => 'sbm-label-top'
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
            'serviceId' => array(
                'required' => true
            ),
            'stationId' => array(
                'required' => true
            ),
            'semaine' => array(
                'required' => true
            ),
            'm1' => array(
                'required' => false
            ),
            's1' => array(
                'required' => false
            ),
            'm2' => array(
                'required' => false
            ),
            's2' => array(
                'required' => false
            ),
            'm3' => array(
                'required' => false
            ),
            's3' => array(
                'required' => false
            ),
            'distance' => array(
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
            'typeArret' => array(
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
            'commentaire' => array(
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
    }

    public function setData($data)
    {
        // adapte le format des time pour les éléments DateTimeLocal du formulaire
        $elementsTime = array(
            'm1',
            'm2',
            'm3',
            's1',
            's2',
            's3'
        );
        for ($i = 0; $i < count($elementsTime); $i ++) {
            if (isset($data[$elementsTime[$i]])) {
                $dte = new \DateTime($data[$elementsTime[$i]]);
                $data[$elementsTime[$i]] = $dte->format('H:i');
            }
        }
        // appelle la méthode de ZF2
        parent::setData($data);
    }
}