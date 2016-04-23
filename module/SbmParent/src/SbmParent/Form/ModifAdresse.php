<?php
/**
 * Formulaire de modification de l'adresse d'un responsable
 *
 * La méthode valid() vérifie si l'adresse a changé et dans ce cas place 
 * l'ancienne adresse dans les data du formulaire.
 * 
 * @project sbm
 * @package SbmParent/Form
 * @filesource ModifAdresse.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 avr. 2016
 * @version 2016-2
 */
namespace SbmParent\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\FormInterface;

class ModifAdresse extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('responsable');
        $this->add(array(
            'type' => 'hidden',
            'name' => 'responsableId'
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
            'name' => 'adresseL1',
            'type' => 'SbmCommun\Form\Element\Adresse',
            'attributes' => array(
                'id' => 'adresseL1',
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
            'name' => 'adresseL2',
            'type' => 'SbmCommun\Form\Element\Adresse',
            'attributes' => array(
                'id' => 'adresseL2',
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
            'name' => 'codePostal',
            'type' => 'SbmCommun\Form\Element\CodePostal',
            'attributes' => array(
                'id' => 'codePostal',
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
                'id' => 'communeId',
                'class' => 'sbm-width-45c'
            ),
            'options' => array(
                'label' => 'Commune',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Choisissez une commune',
                'disable_inarray_validator' => true,
                'allow_empty' => false,
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'telephoneF',
            'type' => 'SbmCommun\Form\Element\Telephone',
            'attributes' => array(
                'id' => 'telephoneF',
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
            'name' => 'telephoneP',
            'type' => 'SbmCommun\Form\Element\Telephone',
            'attributes' => array(
                'id' => 'telephoneF',
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
            'name' => 'telephoneT',
            'type' => 'SbmCommun\Form\Element\Telephone',
            'attributes' => array(
                'id' => 'telephoneF',
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
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer les modifications',
                'id' => 'responsable-submit',
                'autofocus' => 'autofocus',
                'class' => 'button default submit left-95px'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'responsable-cancel',
                'class' => 'button default cancel left-10px'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'adresseL1' => array(
                'name' => 'adresseL1',
                'required' => true
            ),
            'adresseL2' => array(
                'name' => 'adresseL2',
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
            'telephoneF' => array(
                'name' => 'telephoneF',
                'required' => true
            ),
            'telephoneP' => array(
                'name' => 'telephoneF',
                'required' => false
            ),
            'telephoneT' => array(
                'name' => 'telephoneF',
                'required' => false
            )
        );
    }
}