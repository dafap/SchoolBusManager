<?php
/**
 * Données de la table système `libelles`
 *
 * Fichier permettant de recharger la table à partir du module SbmInstallation, action create
 * 
 * @project sbm
 * @package SbmInstallation
 * @filesource data.system.libelles.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 Mar 2015
 * @version 2014-1
 */
return array(
    array(
        'nature' => 'Caisse',
        'code' => 1,
        'libelle' => 'régisseur',
        'ouvert' => 1
    ),
    array(
        'nature' => 'Caisse',
        'code' => 2,
        'libelle' => 'comptable',
        'ouvert' => 1
    ),
    array(
        'nature' => 'ModeDePaiement',
        'code' => 1,
        'libelle' => 'chèque',
        'ouvert' => 1
    ),
    array(
        'nature' => 'ModeDePaiement',
        'code' => 2,
        'libelle' => 'espèces',
        'ouvert' => 1
    ),
    array(
        'nature' => 'ModeDePaiement',
        'code' => 3,
        'libelle' => 'CB',
        'ouvert' => 0
    ),
    array(
        'nature' => 'ModeDePaiement',
        'code' => 4,
        'libelle' => 'Titre individuel',
        'ouvert' => 1
    )
);