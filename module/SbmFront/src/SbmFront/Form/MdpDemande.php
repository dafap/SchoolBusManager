<?php
/**
 * Formulaire de demande d'un mot de passe
 *
 * Un nouveau mot de passe sera renvoyé à l'adresse indiquée si elle correspond au compte d'un utilisateur.
 * 
 * @project sbm
 * @package SbmFront/Form
 * @filesource MdpDemande.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 févr. 2015
 * @version 2015-1
 */
namespace SbmFront\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class MdpDemande extends Form implements InputFilterProviderInterface
{
    private $sm;

    public function __construct($sm, $param = 'mdp-demande')
    {
        $this->sm = $sm;
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
            'name' => 'email',
            'type' => 'Zend\Form\Element\Email',
            'attributes' => array(
                'id' => 'user-email',
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
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Envoyer la demande',
                'id' => 'responsable-submit',
                'class' => 'default left-95px'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'responsable-cancel',
                'class' => 'default left-10px'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        $db = $this->sm->get('Sbm\Db\DbLib');
        return array(
            'email' => array(
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
            )
        );
    }
}