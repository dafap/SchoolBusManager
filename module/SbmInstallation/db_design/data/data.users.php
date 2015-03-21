<?php
use Zend\Math\Rand;
/**
 * Données de la table `users`
 *
 * Fichier permettant de recharger la table à partir du module SbmInstallation, action create
 *
 * @project sbm
 * 
 * @package SbmInstallation
 * @filesource data.users.php
 *             @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 *         @date 23 oct. 2014
 * @version 2014-1
 */
return array(
    array(
        'token' => md5(Rand::getBytes(16)),
        'tokenalive' => 1,
        'gds' => Rand::getBytes(8),
        'categorieId' => 255,
        'titre' => 'Superviseur',
        'nom' => 'sadmin',
        'prenom' => 'par défaut',
        'email' => 'dafap@dafap.fr'
    ),
    array(
        'token' => md5(Rand::getBytes(16)),
        'tokenalive' => 1,
        'gds' => Rand::getBytes(8),
        'categorieId' => 254,
        'titre' => 'Administrateur',
        'nom' => 'POMIROL',
        'prenom' => 'Alain',
        'email' => 'pomirol@gmail.com'
    ),
    array(
        'token' => md5(Rand::getBytes(16)),
        'tokenalive' => 1,
        'gds' => Rand::getBytes(8),
        'categorieId' => 253,
        'titre' => 'Gestionnaire',
        'nom' => 'POMIROL',
        'prenom' => 'Alain',
        'email' => 'alain.pomirol@dafap.fr'
    )
);