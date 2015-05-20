<?php
/**
 * Formulaire de changement d'email
 *
 * Mot de passe et le nouvel email avec confirmation.
 * 
 * @project sbm
 * @package SbmFront/Form
 * @filesource EmailChange.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2015
 * @version 2015-1
 */
namespace SbmFront\Form;

use Zend\InputFilter\InputFilterProviderInterface;
use SbmCommun\Form\AbstractSbmForm;
use Zend\ServiceManager\ServiceLocatorInterface;

class EmailChange extends AbstractSbmForm implements InputFilterProviderInterface
{
    /**
     * Service manager (nécessaire pour vérifier l'email)
     *
     * @var ServiceLocatorInterface
     */
    private $sm;
    
    public function __construct(ServiceLocatorInterface $sm)
    {
        $this->sm = $sm;
        parent::__construct('mdp');
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
                'id' => 'mdp',
                'class' => 'sbm-mdp',
                'autofocus' => 'autofocus'
            ),
            'options' => array(
                'label' => 'Donnez votre mot de passe',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));        
        $this->add(array(
            'name' => 'email_new',
            'type' => 'Zend\Form\Element\Email',
            'attributes' => array(
                'id' => 'front-email',
                'class' => '',
            ),
            'options' => array(
                'label' => 'Donnez votre nouvel email',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'email_ctrl',
            'type' => 'Zend\Form\Element\Email',
            'attributes' => array(
                'id' => 'front-email',
                'class' => '',
            ),
            'options' => array(
                'label' => 'Confirmez cet email',
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
                'value' => 'Envoyer la demande',
                'id' => 'responsable-submit',
                'autofocus' => 'autofocus',
                'class' => 'button default submit'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'responsable-cancel',
                'class' => 'button default cancel'
            )
        ));
    }
    
    public function getInputFilterSpecification()
    {
        $db = $this->sm->get('Sbm\Db\DbLib');
        return array(
            'mdp' => array(
                'name' => 'mdp',
                'required' => true
            ),
            'email_new' => array(
                'name' => 'email_new',
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
                        'name' => 'Zend\Validator\Db\NoRecordExists',
                        'options' => array(
                            'table' => $db->getCanonicName('users', 'table'),
                            'field' => 'email',
                            'adapter' => $this->sm->get('Zend\Db\Adapter\Adapter')
                        )
                    )
                )
            ),
            'email_ctrl' => array(
                'name' => 'email_ctrl',
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
                            'token' => 'email_new'
                        )
                    )
                )
            )
        );
    }
} 