<?php
/**
 * Paramètres du plugin PayFiP
 *
 * @project project_name
 * @package package_name
 * @filesource payfip.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 juin 2019
 * @version 2019-2.5.0
 */
return [
    'saisie' => [
        'TEST' => 'T',
        'ACTIVATION' => 'X',
        'PRODUCTION' => 'W'
    ],
    'urlpaiement' => 'https://www.tipi.budget.gouv.fr/tpa/paiementws.web?idop=',
    'urlnotification' => 'http://alain.pomirol.free.fr/notification/index.php',
	//'urlnotification' => 'http://notification.millaugrandscausses.dafap.fr/paiement/notification',
    //'urlnotification' => 'http://www.transports-millaugrandscausses.fr/paiement/notification',
    'montantmini' => 100,
    'montantmaxi' => 9999999,
    'creerPaiementSecurise' => [
        'RequestParameters' => [
            'arg0' => [
                'exer',
                'mel',
                'montant',
                'numcli',
                'objet',
                'refdet',
                'saisie',
                'urlnotif',
                'urlredirect'
            ]
        ],
        'ResponseParameters' => [
            'return' => [
                'idOp'
            ]
        ]
    ],
    'recupererDetailPaiementSecurise' => [
        'RequestParameters' => [
            'arg0' => [
                'idOp'
            ]
        ],
        'ResponseParameters' => [
            'return' => [
                'dattrans',
                'exer',
                'heurtrans',
                'idOp',
                'mel',
                'montant',
                'numauto',
                'numcli',
                'objet',
                'refdet',
                'resultrans',
                'saisie'
            ]
        ]
    ],
    'recupererDetailClient' => [
        'RequestParameters' => [
            'arg0' => [
                'numCli'
            ]
        ],
        'ResponseParameters' => [
            'return' => [
                'libelleN1',
                'libelleN2',
                'libelleN3',
                'numcli'
            ]
        ]
    ]
];