<?php
/**
 * Paramétrage du module d'envoi de mails
 *
 * Les paramètres relatifs au site et au client sont dans config/autoload/sbm.local.php
 * 
 * @project sbm
 * @package DafapMail/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 mai 2015
 * @version 2015-1
 */
return array(
    'sbm' => array(
        'mail' => array(
            'transport' => array(
                /*'mode' => 'smtp', // 'smtp' ou 'sendmail'
                'smtpOptions' => array(
                    'host' => 'smtp.free.fr',
                    'port' => '25', // 25, 587 ou 2525 si connexion TLS ; 465 ou 25025 si connexion SSL
                    'connexion_class' => 'plain',
                    'connexion_config' => array(
                        'username' => '',
                        'password' => '',
                        'from' => ''
                    )
                ),*/
                'transportSsl' => array(
                    'use_ssl' => false,
                    'connection_type' => 'tls' // ssl | tls
                )
            ),            
            'message' => array(
                'type' => 'text/html',
                'html_encoding' => \Zend\Mime\Mime::ENCODING_8BIT,
                'message_encoding' => 'UTF-8'
            )
        )
    )
);
 