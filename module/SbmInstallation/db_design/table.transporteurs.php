<?php
/**
 * Structure de la table des `transporteurs`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.transporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 févr. 2014
 * @version 2014-1
 */
return array(
    'name' => 'transporteurs',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false, // si false, on ne touche pas à la structure dans Create::createOrAlterEntity() - true par défaut
    'add_data' => false, // si false, on ne fait rien dans Create::addData() - true par défaut ; sans effet sur une vue
    'structure' => array(
        'fields' => array(
            'transporteurId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'nom' => 'varchar(30) NOT NULL',
            'adresse1' => 'varchar(38) NOT NULL DEFAULT ""',
            'adresse2' => 'varchar(38) NOT NULL DEFAULT ""',
            'codePostal' => 'varchar(5) NOT NULL',
            'communeId' => 'varchar(6) NOT NULL',
            'telephone' => 'varchar(10) NOT NULL DEFAULT ""',
            'fax' => 'varchar(10) NOT NULL DEFAULT ""',
            'email' => 'varchar(80) NOT NULL DEFAULT ""',
            'siret' => 'varchar(14) NOT NULL DEFAULT ""',
            'naf' => 'varchar(5) NOT NULL DEFAULT ""',
            'tvaIntraCommunautaire' => 'varchar(13) NOT NULL DEFAULT ""',
            'rib_titulaire' => 'varchar(32) NOT NULL DEFAULT ""',
            'rib_domiciliation' => 'varchar(24) NOT NULL DEFAULT ""',
            'rib_bic' => 'varchar(11) NOT NULL DEFAULT ""',
            'rib_iban' => 'varchar(34) NOT NULL DEFAULT ""'
        ),
        'primary_key' => array(
            'transporteurId'
        ),
        'foreign key' => array(
            array(
                'key' => 'communeId',
                'references' => array(
                    'table' => 'communes',
                    'fields' => array(
                        'communeId'
                    ),
                    'on' => array(
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    )
                )
            )
        ),
        
        // 'keys' => array(
        // 'noms' => array('fields' => array('nom',),),
        // 'membres_alpha' => array('fields' => array('membre',),),
        // 'desservies_alpha' => array('fields' => array('desservie',),),
        // ),
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ),
    
    // 'data' => include __DIR__ . '/data/data.transporteurs.php'
    // 'data' => array('after' => 'communes','include' => __DIR__ . '/data/data.transporteurs.php')
    'data' => __DIR__ . '/data/data.transporteurs.php'
);