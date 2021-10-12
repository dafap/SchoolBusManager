<?php
/**
 * Configuration générale du plugin de PayBox
 *
 * Paramètres d'accès à l'API de PayBox
 *
 * @project sbm
 * @package SbmPaiement/Plugin/Paybox/config
 * @filesource paybox.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 avr. 2020
 * @version 2020-2.6.0
 */
if (defined('TEST') || getenv('APPLICATION_ENV') == 'development') {
    $urlpaiement = [
        'PREPROD' => 'https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi'
    ];
} else {
    $urlpaiement = [
        'PRINCIPAL' => 'https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi',
        'SECONDAIRE' => 'https://tpeweb1.paybox.com/cgi/MYchoix_pagepaiement.cgi'
    ];
}
return [
    'urlpaiement' => $urlpaiement,
    'montantmini' => 100,
    'montantmaxi' => 9999999,
    'formulaire' => [
        'variables' => [
            'PBX_SITE' => '',
            'PBX_RANG' => '',
            'PBX_IDENTIFIANT' => '',
            'PBX_TOTAL' => '',
            'PBX_DEVISE' => 978,
            'PBX_CMD' => '',
            'PBX_PORTEUR' => '',
            'PBX_REPONDRE_A' => '',
            'PBX_EFFECTUE' => '',
            'PBX_REFUSE' => '',
            'PBX_ANNULE' => '',
            'PBX_ATTENTE' => '',
            'PBX_RETOUR' => "auto:A;erreur:E;montant:M;ref:R;idtrans:S;datetrans:W;heuretrans:Q;g3ds:G;carte:C;bin6:N;bin2:J;pays:Y;ip:I;sign:K",
            'PBX_HASH' => 'SHA512',
            'PBX_TIME' => '',
            'PBX_RUF1' => 'POST'
        ],
        'abonnement' => [
            'PBX_2MONT' => '',
            'PBX_NBPAIE' => '02',
            'PBX_FREQ' => '01',
            'PBX_QUAND' => '00'
        ]
    ]
];
