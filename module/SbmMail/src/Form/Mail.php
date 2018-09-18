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
 * @date 13 sept. 2018
 * @version 2018-2.4.5
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
        $this->add([
            'name' => 'userId',
            'type' => 'hidden'
        ]);
        $this->add(
            [
                'name' => 'csrf',
                'type' => 'Zend\Form\Element\Csrf',
                'options' => [
                    'csrf_options' => [
                        'timeout' => 180
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'text',
                'name' => 'subject',
                'attributes' => [
                    'id' => 'mail-subject'
                ],
                'options' => [
                    'label' => 'Sujet',
                    'label_attributes' => [
                        'class' => 'sbm-label'
                    ],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'body',
                'attributes' => [
                    'id' => 'mail-body'
                ],
                'options' => [
                    'label' => 'Message',
                    'label_attributes' => [],
                    'error_attributes' => [
                        'class' => 'sbm-error'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Envoyer le message',
                    'id' => 'mail-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'button submit left-95px'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'mail-cancel',
                    'class' => 'button cancel left-10px'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'subject' => [
                'name' => 'subject',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'body' => [
                'name' => 'body',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ]
        ];
    }
}