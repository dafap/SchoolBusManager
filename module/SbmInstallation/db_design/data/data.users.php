<?php
/**
 * Données de la table `users`
 *
 * Fichier permettant de recharger la table à partir du module SbmInstallation, action create
 * 
 * @project sbm
 * @package SbmInstallation
 * @filesource data.users.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 oct. 2014
 * @version 2014-1
 */
return array(
    array(
        'confirme' => 1,
        'categorie' => 4,
        'titre' => 'Superviseur',
        'nom' => 'sadmin',
        'nomSA' => 'sadmin',
        'prenom' => 'par défaut',
        'prenomSA' => 'par défaut',
        'adresseL1' => '42 avenue Jeanne de Devant',
        'adresseL2' => '',
        'codePostal' => '33210',
        'communeId' => '33227',
        'telephoneF' => '',
        'telephoneP' => '0607879444',
        'telephoneT' => '',
        'email' => 'dafap@dafap.fr',
        'mdp' => '3e150508a043250c599cdbdc993c5934da7b468a', // mdp dafap court
        'temoin' => 'mdp dafap court'
    ),
    array(
        'confirme' => 1,
        'categorie' => 3,
        'titre' => 'Administrateur',
        'nom' => 'POMIROL',
        'nomSA' => 'POMIROL',
        'prenom' => 'Alain',
        'prenomSA' => 'Alain',
        'adresseL1' => '42 avenue Jeanne de Devant',
        'adresseL2' => '',
        'codePostal' => '33210',
        'communeId' => '33227',
        'telephoneF' => '0556632496',
        'telephoneP' => '0607879444',
        'telephoneT' => '',
        'email' => 'pomirol@gmail.com',
        'mdp' => '5baf3ab02268945dff1c93eb0e4a242fb4befab1', // mdp perso long
        'temoin' => 'mdp perso long'
    )
);