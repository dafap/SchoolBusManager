<?php
/**
 * Formulaire de login de la page d'accueil
 *
 * Compatible ZF3
 * 
 * @project sbm
 * @package SbmFront/Form
 * @filesource Login.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2016
 * @version 2016-2
 */
namespace SbmFront\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class Login extends AbstractSbmForm implements InputFilterProviderInterface
{
    private $canonic_name;
    private $db_adapter;
    
    public function __construct($canonic_name, $db_adapter)
    {
        $this->canonic_name = $canonic_name;
        $this->db_adapter = $db_adapter;
        parent::__construct('login');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'email',
            'type' => 'Zend\Form\Element\Email',
            'attributes' => array(
                'id' => 'front-email',
                'class' => 'sbm-page1',
                'autofocus' => 'autofocus'
            ),
            'options' => array(
                'label' => 'Email',
                'label_attributes' => array(
                    'class' => 'sbm-label-page1'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'mdp',
            'type' => 'password',
            'attributes' => array(
                'id' => 'front-mdp',
                'class' => 'sbm-page1'
            ),
            'options' => array(
                'label' => 'Mot de passe',
                'label_attributes' => array(
                    'class' => 'sbm-label-page1'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'signin',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Connexion',
                'id' => 'sbm-signin',
                'class' => 'default sbm-signin'
            )
        ));
        $this->add(array(
            'name' => 'signup',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Créer un compte',
                'id' => 'sbm-signup',
                'class' => 'default sbm-signup'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'email' => array(
                'name' => 'email',
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
                        'name' => 'Zend\Validator\EmailAddress'
                    ),
                    array(
                        'name' => 'Zend\Validator\Db\RecordExists',
                        'options' => array(
                            'table' => $this->canonic_name,
                            'field' => 'email',
                            'adapter' => $this->db_adapter
                        )
                    )
                )
            ),
            'mdp' => array(
                'name' => 'mdp',
                'required' => true,
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
}
 