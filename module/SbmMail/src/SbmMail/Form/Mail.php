<?php
/**
 * Formulaire d'envoi d'un message
 *
 * Ce formulaire demande le userId mais le traitement ne l'utilise pas (c'est un leure)
 * De plus, dans la view, le textarea n'est pas affiché. Un jQuery le créera à la volée 
 * à la fin du chargement de la page, imposant l'usage de tinymce. (sécurité)
 * 
 * Le nom par défaut est 'mail'. Pour donner un autre nom, utiliser la méthode setName().
 * Pour donner des options au formulaire, utiliser la méthode setOptions()
 * 
 * @project sbm
 * @package SbmMail/Form
 * @filesource Mail.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 août 2016
 * @version 2016-2.2.0
 */
namespace SbmMail\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class Mail extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('mail');
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
                'name' => 'subject',
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
                'name' => 'body',
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