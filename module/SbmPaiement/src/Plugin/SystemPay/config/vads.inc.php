<?php
/**
 * Liste des champs `vads_` indexés par leur code d'erreur retourné dans `vads_extra_result`
 *
 * L'index de chaque champ est le code erreur qu'il peut provoquer.
 * 
 * @project sbm
 * @package SbmPaiement/Plugin/SystemPay/config
 * @filesource vads.inc.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 sept. 2018
 * @version 2018-2.4.5
 */

/** 
 * @see `Guide_d'implementation_formulaire_paiement30012015081841.pdf`
 * (Guide d'implementation du formulaire de paiement Systempay 2.2 - Version du document 3.0)
 */
return [
        '02' => 'vads_site_id',
        '11' => 'vads_ctx_mode',
        '03' => 'vads_trans_id',
        '04' => 'vads_trans_date',
        '09' => 'vads_amount',
        '10' => 'vads_currency',
        '47' => 'vads_action_mode',
        '46' => 'vads_page_action',
        '01' => 'vads_version',
        '07' => 'vads_payment_config',
        '06' => 'vads_capture_delay',
        '05' => 'vads_validation_mode',
        '15' => 'vads_cust_email',
        '16' => 'vads_cust_id', // responsableId
        '104' => 'vads_cust_first_name', // prénom
        '105' => 'vads_cust_last_name', // nom
        '13' => 'vads_order_id', // référence de la commande (avec les responsableId-eleve1Id-eleve2Id...)
        '32' => 'vads_theme_config',
        '24' => 'vads_url_success',
        '25' => 'vads_url_refused',
        '27' => 'vads_url_cancel',
        '29' => 'vads_url_error',
        '33' => 'vads_url_check',
        '34' => 'vads_redirect_success_timeout',
        '35' => 'vads_redirect_success_message',
        '36' => 'vads_redirect_error_timeout',
        '37' => 'vads_redirect_error_message'
    ]; 