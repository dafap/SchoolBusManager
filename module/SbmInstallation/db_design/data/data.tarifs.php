<?php
/**
 * Données de la table `tarifs`
 *
 * Fichier permettant de recharger la table à partir du module SbmInstallation, action create
 * 
 * @project sbm
 * @package SbmInstallation
 * @filesource data.tarifs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 Aug 2016
 * @version 2014-1
 */
return array(
    array(
        'tarifId' => 1, 
        'selection' => 0, 
        'montant' => 50.00, 
        'nom' => 'Tarif annuel', 
        'rythme' => 1, 
        'grille' => 1, 
        'mode' => 3, 
    ),
    array(
        'tarifId' => 2, 
        'selection' => 0, 
        'montant' => 15.00, 
        'nom' => 'Duplicata de carte', 
        'rythme' => 1, 
        'grille' => 2, 
        'mode' => 3, 
    ),
);