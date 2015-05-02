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
 * @date 5 févr. 2014
 * @version 2014-1
 */

/**
 * ***************************************************************************
 * structure de la table 'tarifs'
 * Table InnoDB encodée utf8
 * Description des champs
 * - tarifId est un auto-incrément
 * - montant est le montant du tarif en euros
 * - nom est le libellé de ce tarif
 * - attributs indique les attributs de ce tarif selon la règle suivante
 * 1 tarif annuel
 * 2 tarif semestriel
 * 4 tarif trimestriel
 * 8 tarif mensuel
 * 16 tarif de la grille 1
 * 32 tarif de la grille 2
 * 64 tarif de la grille 3
 * 128 tarif de la grille 4
 * 256 tarif utilisé pour le paiement par prélèvement
 * 512 tarif utilisé pour le paiement en ligne par CB
 * 1024 tarif utilisé pour le paiement direct par chèque, CB ou en espèces
 * 2048 tarif utilisé pour le paiement par virement
 * Les attributs de 1 à 8 sont exclusifs
 * Les attributs de 16 à 128 sont exclusifs
 * Les attributs de 256 à 2048 sont exclusifs
 * Ces 3 groupes d'attibuts se combinent par "Et binaire"
 * ***************************************************************************
 */
return array(
    'name' => 'tarifs',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure' => array(
        'fields' => array(
            'tarifId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'montant' => 'decimal(10,2) NOT NULL DEFAULT "0.00"',
            'nom' => 'varchar(48) NOT NULL',
            'rythme' => 'int(4) NOT NULL DEFAULT "1"',
            'grille' => 'int(4) NOT NULL DEFAULT "1"',
            'mode' => 'int(4) NOT NULL DEFAULT "3"'
        ),
        'primary_key' => array(
            'tarifId'
        ),
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ),
    
    // 'data' => include __DIR__ . '/data/data.tarifs.php'
    //'data' => array('after' => array(),'include' => __DIR__ . '/data/data.tarifs.php')
    'data' => __DIR__ . '/data/data.tarifs.php'
);