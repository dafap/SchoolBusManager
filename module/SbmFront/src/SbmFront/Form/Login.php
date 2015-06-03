<?php
/**
 * Formulaire de login de la page d'accueil
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmFront/Form
 * @filesource Login.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 févr. 2015
 * @version 2015-1
 */
namespace SbmFront\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class Login extends AbstractSbmForm implements InputFilterProviderInterface
{
    private $sm;
    
    public function __construct($sm, $param = 'login')
    {
        $this->sm = $sm;
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
        $db = $this->sm->get('Sbm\Db\DbLib');
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
                            'table' => $db->getCanonicName('users', 'table'),
                            'field' => 'email',
                            'adapter' => $this->sm->get('Zend\Db\Adapter\Adapter')
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
 