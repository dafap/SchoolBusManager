<?php
/**
 * Formulaire de saisie et modification d'une commune
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource Commune.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Commune extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct($param = 'commune')
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
            'name' => 'communeId',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-codeid',
                'autofocus' => 'autofocus',
                'class' => 'sbm-width-5c'
            ),
            'options' => array(
                'label' => 'Code INSEE de la commune',
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
                'id' => 'commune-nom',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Nom de la commune en majuscules',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'nom_min',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-nom-min',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Nom de la commune en minuscules',
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
                'id' => 'commune-alias',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Autre nom (en majuscules)',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'alias_min',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-alias-min',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Autre nom (en minuscules)',
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
                'id' => 'commune-aliascg',
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
            'name' => 'codePostal',
            'type' => 'SbmCommun\Form\Element\CodePostal',
            'attributes' => array(
                'id' => 'commune-codepostal',
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
            'name' => 'departement',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-departement',
                'class' => 'sbm-width-5c'
            ),
            'options' => array(
                'label' => 'Code du département',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'canton',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-canton',
                'class' => 'sbm-width-5c'
            ),
            'options' => array(
                'label' => 'Code du canton',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'population',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-population',
                'class' => 'sbm-width-10c'
            ),
            'options' => array(
                'label' => 'Population',
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
            'name' => 'membre',
            'attributes' => array(
                'id' => 'commune-membre',
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Commune membre',
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
                'id' => 'commune-desservie',
                'class' => 'sbm-checkbox'
            ),
            'options' => array(
                'label' => 'Commune desservie',
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
                'id' => 'commune-visible',
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
                'id' => 'commune-submit',
                'class' => 'button default submit'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'commune-cancel',
                'class' => 'button default cancel'
            )
        ));
    }
    
    public function getInputFilterSpecification()
    {
        return array(
            'communeId' => array(
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
                        'name' => 'StringLength',
                        'options' => array(
                            'min' => 5,
                            'max' => 6
                        )
                    )
                )
            ),
            'nom' => array(
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
            'nom_min' => array(
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
            'alias' => array(
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
            'alias_min' => array(
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
            'aliasCG' => array(
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
            'codePostal' => array(
                'required' => true
            ),
            'departement' => array(
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
                        'name' => 'StringLength',
                        'options' => array(
                            'min' => 2,
                            'max' => 3
                        )
                    )
                )
            ),
            'canton' => array(
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'Digits'
                    )
                )
            ),
            'population' => array(
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'Digits'
                    )
                )
            ),
        );
    }

    public function modifFormForEdit()
    {
        $this->remove('communeId');
        $this->get('nom')->setAttribute('autofocus', 'autofocus');
        $this->add(array(
            'name' => 'communeId',
            'type' => 'hidden'
        ));
        $this->add(array(
            'name' => 'communeInsee',
            'type' => 'text',
            'attributes' => array(
                'id' => 'commune-codeid',
                'disabled' => 'disabled',
                'class' => 'form commune codeid'
            ),
            'options' => array(
                'label' => 'Code INSEE de la commune',
                'label_attributes' => array(
                    'class' => 'form commune label label-codeid'
                ),
                'error_attributes' => array(
                    'class' => 'form commune error error-codeid'
                )
            )
        ));
        return $this;
    }

    public function setData($data)
    {
        parent::setData($data);
        if ($this->has('communeInsee')) {
            $e = $this->get('communeInsee');
            $e->setValue($this->get('communeId')
                ->getValue());
        }
    }
}