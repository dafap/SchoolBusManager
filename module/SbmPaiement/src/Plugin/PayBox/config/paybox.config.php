<?php
/**
 * Configuration générale du plugin de PayBox
 *
 * Paramètres d'accès à l'API de PayBox
 * La clé 'extraction' décrit les entêtes des colonnes utiles du fichier MS Excel d'extraction
 * des transactions de Paybox donné par le backOffice.
 *
 * @project sbm
 * @package SbmPaiement/Plugin/Paybox/config
 * @filesource paybox.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 août 2020
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
    ],
    'extraction' => [
        'columns' => [
            'type_transaction' => 'G',
            'statut_translation' => 'H',
            'date_heure' => 'C',
            'reference-commande' => 'D',
            'montant' => 'E',
            'num_transaction' => 'A',
            'num_autorisation' => 'AH',
            'moyen_de_paiement' => 'J',
            'pays_IP' => 'K',
            'pays_porteur' => 'L',
            'motif_refus' => 'R',
            'type_appel' => 'U',
            'statut_3ds' => 'V'
        ],
        'head' => [

            'type_transaction' => 'Type de transaction',
            'statut_translation' => 'Statut de la transaction',
            'date_heure' => 'Date & Heure',
            'reference-commande' => 'Référence commande',
            'montant' => 'Montant',
            'num_transaction' => 'Num. transaction',
            'num_autorisation' => 'Num. autorisation',
            'moyen_de_paiement' => 'Moyen de paiement',
            'pays_IP' => 'Pays IP',
            'pays_porteur' => 'Pays Porteur',
            'motif_refus' => 'Motif refus',
            'type_appel' => "Type d'appel",
            'statut_3ds' => 'Statut 3DS'
        ]
    ]
];
