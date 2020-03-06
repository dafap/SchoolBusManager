<?php
/**
 * Structure de la table des `tarifs`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.tarifs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 mars 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;

/**
 * ***************************************************************************
 *
 * @formatter off
 * structure de la table 'tarifs' Table InnoDB encodée utf8
 * Description des champs
 * - tarifId est un auto-incrément
 * - selection est un drapeau (boolean)
 * - montant est le montant du tarif en euros
 * - nom est le libellé de ce tarif
 * - duplicata, indique la nature de la grille tarifaire
 * - grille est le numéro de la grille tarifaire. Une stratégie permet de décoder.
 * - reduit indique la nature du tarif
 * - mode est le code du mode de calcul : 1 dégressif, 2 linéaire à l'unité
 * - seuil est le seuil de déclanchement du tarif lorsqu'il est dégressif
 * Un index est posé sur le champ 'grille'
 * @formatter on
 * ***************************************************************************
 */
return [
    'name' => 'tarifs',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure' => [
        'fields' => [
            'tarifId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'montant' => 'decimal(10,2) NOT NULL DEFAULT "0.00"',
            'nom' => 'varchar(48) NOT NULL',
            'duplicata' => 'int(1) NOT NULL DEFAULT "0"',
            'grille' => 'int(3) NOT NULL DEFAULT "1"',
            'reduit' => 'int(1) NOT NULL DEFAULT "0"',
            'mode' => 'int(3) NOT NULL DEFAULT "1"',
            'seuil' => 'int(3) NOT NULL DEFAULT "1"'
        ],
        'primary_key' => [
            'tarifId'
        ],
        'keys' => [
            'idx_grille' => [
                'unique' => true,
                'fields' => [
                    'duplicata',
                    'grille',
                    'reduit',
                    'seuil'
                ]
            ]
        ],
        'engine' => 'InnoDb',
        'charset' => 'utf8mb4',
        'collate' => 'utf8mb4_unicode_ci'
    ],

    // 'data' => include __DIR__ . '/data/data.tarifs.php'
    // 'data' => ['after' => [],'include' => __DIR__ . '/data/data.tarifs.php']
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.tarifs.php')
];