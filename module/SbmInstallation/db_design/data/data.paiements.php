<?php
/**
 * Données de la table `paiements`
 *
 * Fichier permettant de recharger la table à partir du module SbmInstallation, action create
 * 
 * @project sbm
 * @package SbmInstallation
 * @filesource data.paiements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 Feb 2015
 * @version 2014-1
 */
return array(
    array(
        'paiementId' => 1, 
        'dateDepot' => '2015-01-22 16:27:00', 
        'datePaiement' => '2015-01-22 09:10:00', 
        'dateValeur' => '2015-01-22', 
        'responsableId' => 1, 
        'anneeScolaire' => '2014-2015', 
        'exercice' => 2015, 
        'montant' => 125.00, 
        'codeModeDePaiement' => 1, 
        'codeCaisse' => 2, 
        'banque' => 'BPACA', 
        'titulaire' => 'MARTIN', 
        'reference' => '12345678F90', 
    ),
    array(
        'paiementId' => 2, 
        'dateDepot' => null, 
        'datePaiement' => '2015-01-22 17:00:00', 
        'dateValeur' => null, 
        'responsableId' => 1, 
        'anneeScolaire' => '2014-2015', 
        'exercice' => 2015, 
        'montant' => 47.00, 
        'codeModeDePaiement' => 2, 
        'codeCaisse' => 1, 
        'banque' => '', 
        'titulaire' => '', 
        'reference' => '', 
    ),
    array(
        'paiementId' => 3, 
        'dateDepot' => null, 
        'datePaiement' => '2015-01-23 09:42:00', 
        'dateValeur' => '2015-01-31', 
        'responsableId' => 5, 
        'anneeScolaire' => '2014-2015', 
        'exercice' => 2015, 
        'montant' => 230.00, 
        'codeModeDePaiement' => 1, 
        'codeCaisse' => 1, 
        'banque' => 'LBP', 
        'titulaire' => 'RAMIREZ Edouardo', 
        'reference' => '33445566F23', 
    ),
);