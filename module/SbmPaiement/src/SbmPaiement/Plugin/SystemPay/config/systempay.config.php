<?php
/**
 * Paramétrage de l'api pour SystemPay 2.2
 *
 * Valeurs conformes à la documentation : Guide d'implementation du formulaire de paiement
 * Version de référence de la documentation : version 3.0
 * 
 * Attention ! Les paramétres du contexte (TEST ou PRODUCTION), de la référence du marchand 
 * et des certificats pour le calcul de la signature sont enregistrés dans la config globale
 * de l'application /config/autoload/sbm.local.php
 * 
 * @project sbm
 * @package SbmPaiement/Model/SystemPay/config
 * @filesource systempay.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 avr. 2015
 * @version 2015-1
 */
return array(
    'url_paiement' => 'https://paiement.systempay.fr/vads-payment/',
    //'url_paiement' => 'http://cc-decazeville-aubin.dafap.fr/notification/paiement.php',
    'url_marchand' => 'https://paiement.systempay.fr/vads-merchant/',
    'vads_action_mode' => 'INTERACTIVE',
    'vads_currency' => '978',
    'vads_page_action' => 'PAYMENT',
    'vads_capture_delay' => '0',
    'vads_validation_mode' => '0',
    'vads_theme_config' => 'CANCEL_FOOTER_MSG_RETURN=Annuler et retourner au site d\'inscription',
    //'vads_theme_config' => 'CANCEL_FOOTER_MSG_RETURN=Annuler et retourner au site d\'inscription;SUCCESS_FOOTER_MSG_RETURN=Retour au site d\'inscription',  
    'vads_version' => 'V2',
    'uniqid_path' => realpath(__DIR__ . '/../../../../../../../data/share'),
    // vads_trans_id_max doit être, d'après la documentation, compris entre 000000 et 899999,
    // doit être unique pour chaque transaction pour une boutique donnée sur la journée.
    // Lorsque la valeur maxi est atteinte, le vads_trans_id repart depuis 000001.
    // Fixer la valeur maxi en tenant compte d'un flux maxi
    'vads_trans_id_max' => 1000
); 