<?php
/**
 * Formulaire d'envoi d'un message
 *
 * Ce formulaire demande le userId mais le traitement ne l'utilise pas (c'est un leure)
 * De plus, dans la view, le textarea n'est pas affiché. Un jQuery le crée à la volée 
 * à la fin du chargement de la page, imposant l'usage de tinymce. (sécurité)
 * 
 * @project sbm
 * @package DafapMail/Form
 * @filesource Mail.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 mai 2015
 * @version 2015-1
 */
namespace DafapMail\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class Mail extends Form implements InputFilterProviderInterface
{

    public function __construct($name = 'mail', $options = array())
    {
        parent::__construct($name, $options);
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
            'type' => 'text',
            'name' => 'subject',
            'attributes' => array(
                'id' => 'mail-subject'
            ),
            'options' => array(
                'label' => 'Sujet',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'body',
            'attributes' => array(
                'id' => 'mail-body'
            ),
            'options' => array(
                'label' => 'Message',
                'label_attributes' => array(),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Envoyer le message',
                'id' => 'mail-submit',
                'autofocus' => 'autofocus',
                'class' => 'button submit left-95px'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'mail-cancel',
                'class' => 'button cancel left-10px'
            )
        ));
    }
    
    public function getInputFilterSpecification()
    {
        return array(
            'subject' => array(
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
            'body' => array(
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim'
                    )
                )
            )
        );
    }
}