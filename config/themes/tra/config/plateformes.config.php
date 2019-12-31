<?php
/**
 * Constantes définissant les plateformes de paiement en ligne et d'envoi de sms
 *
 * @project sbm
 * @package config/themes/arlysere/config
 * @filesource plateformes.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 déc. 2019
 * @version 2019-2.5.4
 */

// paiements en ligne
if (!defined('PLATEFORMEPAIEMENT')) {
    define('PLATEFORMEPAIEMENT', 'PayBox');
}

// envoi de SMS
if (!defined('PLATEFORMESMS')) {
    define('PLATEFORMESMS', 'CleverSms');
}

// envoi de mails en masse
if (!defined('PLATEFORMEMAIL')) {
    define('PLATEFORMEMAIL', 'Mailchimp');
}