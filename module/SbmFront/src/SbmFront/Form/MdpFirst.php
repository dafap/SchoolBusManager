<?php
/**
 * Formulaire de saisie du mot de passe initial
 *
 * L'utilisateur frappe deux fois le mot de passe de son choix.
 * 
 * @project sbm
 * @package SbmFront/Form
 * @filesource MdpFirst.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 févr. 2015
 * @version 2015-1
 */
namespace SbmFront\Form;

use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\Form;

class MdpFirst extends Form implements InputFilterProviderInterface
{

    public function __construct($param = 'mdp-first')
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'userId',
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
            'name' => 'mdp',
            'type' => 'password',
            'attributes' => array(
                'id' => 'mdp-new',
                'autofocus' => 'autofocus',
                'class' => 'sbm-mdp'
            ),
            'options' => array(
                'label' => 'Choisissez un mot de passe',
                'label_attributes' => array(
                    'class' => 'sbm-label-200px'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'ctrl',
            'type' => 'password',
            'attributes' => array(
                'id' => 'ctrl',
                'class' => 'sbm-mdp'
            ),
            'options' => array(
                'label' => 'Confirmez ce mot de passe',
                'label_attributes' => array(
                    'class' => 'sbm-label-200px'
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
                'id' => 'responsable-submit',
                'class' => 'button submit left-95px'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'responsable-cancel',
                'class' => 'button cancel left-10px'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'mdp' => array(
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
                            'min' => 6,
                            'max' => 128
                        )
                    )
                )
            ),
            'ctrl' => array(
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
                        'name' => 'identical',
                        'options' => array(
                            'token' => 'mdp'
                        )
                    )
                )
            )
            
        );
    }
}