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
 * @date 7 avr. 2016
 * @version 2016-2
 */
namespace SbmFront\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class MdpDemande extends AbstractSbmForm implements InputFilterProviderInterface
{
    private $canonic_name;
    private $db_adapter;

    public function __construct($canonic_name, $db_adapter )
    {
        $this->canonic_name = $canonic_name;
        $this->db_adapter = $db_adapter;
        parent::__construct('mdp-demande');
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
            'type' => 'Zend\Form\Element\Email',
            'name' => 'email',
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
            )
        );
    }
    
    public function isValid()
    {
        $result = parent::isValid();
        if (!$result) {
            $e = $this->get('email');
            if (array_key_exists('noRecordFound', $e->getMessages())) {
                $e->setMessages(array('Cet email est inconnu.'));
            }
        }
        return $result;
        
    }
}