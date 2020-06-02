<?php
/**
 * Formulaire d'envoi d'un message
 *
 * @project sbm
 * @package SbmEsendex/src/Form
 * @filesource Sms.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Form;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class Sms extends AbstractSbmForm implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('sms');
        $this->setAttribute('method', 'post');
        $this->add([
            'name' => 'userId',
            'type' => 'hidden'
        ]);
        /*$this->add(
            [
                'name' => 'csrf',
                'type' => 'Zend\Form\Element\Csrf',
                'options' => [
                    'csrf_options' => [
                        'timeout' => 180
                    ]
                ]
            ]);*/
        $this->add(
            [
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'body',
                'attributes' => [
                    'id' => 'sms-body'
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
                    'id' => 'sms-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'button submit default'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'sms-cancel',
                    'class' => 'button cancel default'
                ]
            ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'body' => [
                'name' => 'body',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'StripTags'
                    ]
                ]
            ]
        ];
    }
}