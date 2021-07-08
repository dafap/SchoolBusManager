<?php
/**
 * Formulaire de modification de la configuration du service mail
 *
 * @project sbm
 * @package SbmInstallation/src/Form
 * @filesource ConfigMail.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 juil. 2O21
 * @version 2021-2.6.3
 */
namespace SbmInstallation\Form;

use SbmBase\Model\StdLib;
use Zend\Form\Form;

class ConfigMail extends Form
{

    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');
        $this->add(
            [
                'name' => 'transport|mode',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [],
                'options' => [
                    'label' => 'Mode',
                    'value_options' => [
                        'smtp' => 'smtp',
                        'sendmail' => 'sendmail'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'transport|smtpOptions|name',
                'type' => 'text',
                'attributes' => [],
                'options' => [
                    'label' => 'SmtpOptions : name'
                ]
            ]);
        $this->add(
            [
                'name' => 'transport|smtpOptions|host',
                'type' => 'text',
                'attributes' => [],
                'options' => [
                    'label' => 'SmtpOptions : host'
                ]
            ]);
        $this->add(
            [
                'name' => 'transport|smtpOptions|port',
                'type' => 'text',
                'attributes' => [],
                'options' => [
                    'label' => 'SmtpOptions : port'
                ]
            ]);
        $this->add(
            [
                'name' => 'transport|smtpOptions|connexion_class',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [],
                'options' => [
                    'label' => 'SmtpOptions : connexion_class',
                    'value_options' => [
                        'plain' => 'plain',
                        'login' => 'login',
                        'crammd5' => 'crammd5'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'transport|smtpOptions|connexion_config|username',
                'type' => 'text',
                'attributes' => [],
                'options' => [
                    'label' => 'SmtpOptions - Config connexion : username'
                ]
            ]);
        $this->add(
            [
                'name' => 'transport|smtpOptions|connexion_config|password',
                'type' => 'text',
                'attributes' => [],
                'options' => [
                    'label' => 'SmtpOptions - Config connexion : password'
                ]
            ]);
        $this->add(
            [
                'name' => 'transport|smtpOptions|connexion_config|ssl',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [],
                'options' => [
                    'label' => 'SmtpOptions - Config connexion : ssl',
                    'value_options' => [
                        'none' => 'none',
                        'ssl' => 'ssl',
                        'tls' => 'tls'
                    ]
                ]
            ]);
        $this->add(
            [
                'name' => 'transport|smtpOptions|connexion_config|use_complete_quit',
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => [],
                'options' => [
                    'label' => 'SmtpOptions - Config connexion : use_complete_quit'
                ]
            ]);
        $this->add(
            [
                'name' => 'dkim|params|d',
                'type' => 'text',
                'attributes' => [],
                'options' => [
                    'label' => 'd'
                ]
            ])
            ->add(
            [
                'name' => 'dkim|params|h',
                'type' => 'text',
                'attributes' => [],
                'options' => [
                    'label' => 'h'
                ]
            ])
            ->add(
            [
                'name' => 'dkim|params|s',
                'type' => 'text',
                'attributes' => [],
                'options' => [
                    'label' => 's'
                ]
            ])
            ->add(
            [
                'name' => 'dkim|private_key',
                'type' => 'text',
                'attributes' => [],
                'options' => [
                    'label' => 'private key'
                ]
            ]);
        $this->add(
            [
                'name' => 'message|from|email',
                'type' => 'text',
                'attributes' => [],
                'options' => [
                    'label' => 'De : email'
                ]
            ]);
        $this->add(
            [
                'name' => 'message|from|name',
                'type' => 'text',
                'attributes' => [],
                'options' => [
                    'label' => 'De : name'
                ]
            ]);
        $this->add(
            [
                'name' => 'message|replyTo|email',
                'type' => 'text',
                'attributes' => [],
                'options' => [
                    'label' => 'Répondre à : email'
                ]
            ]);
        $this->add(
            [
                'name' => 'message|replyTo|name',
                'type' => 'text',
                'attributes' => [],
                'options' => [
                    'label' => 'Répondre à : name'
                ]
            ]);
        $this->add(
            [
                'name' => 'message|subject',
                'type' => 'text',
                'attributes' => [],
                'options' => [
                    'label' => 'Sujet des messages envoyés'
                ]
            ]);
        $this->add(
            [
                'name' => 'message|body|text',
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => [],
                'options' => [
                    'label' => 'Format d\'envoi du corps de message : text'
                ]
            ]);
        $this->add(
            [
                'name' => 'message|body|html',
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => [],
                'options' => [
                    'label' => 'Format d\'envoi du corps de message : html'
                ]
            ]);
        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Enregistrer',
                    'id' => 'dump-tables-submit',
                    'autofocus' => 'autofocus',
                    'class' => 'button default calendar top-6px'
                ]
            ]);
        $this->add(
            [
                'name' => 'cancel',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Abandonner',
                    'id' => 'dump-tables-cancel',
                    'class' => 'button default top-6px'
                ]
            ]);
    }

    public function setData($configmail)
    {
        $destinataires = StdLib::getParam('destinataires', $configmail,
            [
                [
                    'email' => '',
                    'name' => ''
                ]
            ]);
        $formdata = [
            'transport|mode' => StdLib::getParamR(explode('|', 'transport|mode'),
                $configmail, ''),
            'transport|smtpOptions|name' => StdLib::getParamR(
                explode('|', 'transport|smtpOptions|name'), $configmail, ''),
            'transport|smtpOptions|host' => StdLib::getParamR(
                explode('|', 'transport|smtpOptions|host'), $configmail, ''),
            'transport|smtpOptions|port' => StdLib::getParamR(
                explode('|', 'transport|smtpOptions|port'), $configmail, ''),
            'transport|smtpOptions|connexion_class' => StdLib::getParamR(
                explode('|', 'transport|smtpOptions|connexion_class'), $configmail, ''),
            'transport|smtpOptions|connexion_config|username' => StdLib::getParamR(
                explode('|', 'transport|smtpOptions|connexion_config|username'),
                $configmail, ''),
            'transport|smtpOptions|connexion_config|password' => StdLib::getParamR(
                explode('|', 'transport|smtpOptions|connexion_config|password'),
                $configmail, ''),
            'transport|smtpOptions|connexion_config|ssl' => StdLib::getParamR(
                explode('|', 'transport|smtpOptions|connexion_config|ssl'), $configmail,
                ''),
            'transport|smtpOptions|connexion_config|use_complete_quit' => StdLib::getParamR(
                explode('|', 'transport|smtpOptions|connexion_config|use_complete_quit'),
                $configmail, ''),
            'message|from|email' => StdLib::getParamR(explode('|', 'message|from|email'),
                $configmail, ''),
            'message|from|name' => StdLib::getParamR(explode('|', 'message|from|name'),
                $configmail, ''),
            'message|replyTo|email' => StdLib::getParamR(
                explode('|', 'message|replyTo|email'), $configmail, ''),
            'message|replyTo|name' => StdLib::getParamR(
                explode('|', 'message|replyTo|name'), $configmail, ''),
            'message|subject' => StdLib::getParamR(explode('|', 'message|subject'),
                $configmail, ''),
            'message|body|text' => StdLib::getParamR(explode('|', 'message|body|text'),
                $configmail, ''),
            'message|body|html' => StdLib::getParamR(explode('|', 'message|body|html'),
                $configmail, ''),
            'dkim|params|d' => StdLib::getParamR(explode('|', 'dkim|params|d'),
                $configmail),
            'dkim|params|h' => StdLib::getParamR(explode('|', 'dkim|params|h'),
                $configmail),
            'dkim|params|s' => StdLib::getParamR(explode('|', 'dkim|params|s'),
                $configmail),
            'dkim|private_key' => StdLib::getParamR(explode('|', 'dkim|private_key'),
                $configmail)
        ];
        for ($i = 0; $i < count($destinataires); $i ++) {
            $name = "destinataires|$i";
            if (! $this->has("$name|email")) {
                $this->add(
                    [
                        'name' => "$name|email",
                        'type' => 'text',
                        'attributes' => [],
                        'options' => [
                            'label' => 'Destinataire des messages reçus : email'
                        ]
                    ]);
                $this->add(
                    [
                        'name' => "$name|name",
                        'type' => 'text',
                        'attributes' => [],
                        'options' => [
                            'label' => 'Destinataire des messages reçus : name'
                        ]
                    ]);
            }
            $formdata["$name|email"] = StdLib::getParam('email', $destinataires[$i], '');
            $formdata["$name|name"] = StdLib::getParam('name', $destinataires[$i], '');
        }
        $this->add(
            [
                'name' => 'addDestinataire',
                'type' => 'button',
                'attributes' => [
                    'onclick' => "js_edit_mail.add($i)"
                ],
                'options' => [
                    'label' => 'Ajouter un destinataire'
                ]
            ]);
        parent::setData($formdata);
    }
}