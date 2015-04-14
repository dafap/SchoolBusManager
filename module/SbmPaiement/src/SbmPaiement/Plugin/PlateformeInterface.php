<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource PlateformeInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2015
 * @version 2015-1
 */
namespace SbmPaiement\Plugin;

interface PlateformeInterface
{

    /**
     * Renvoie l'URL d'appel de la plateforme qui se trouve en config
     */
    public function getUrl();

    /**
     * Reçoit un tableau de données composé des champs suivants :
     * - montant : en euros (il faut le multiplier par 100 ici)
     * - count : le nombre d'échéances (1 si paiement comptant, n si paiement en n fois)
     * - first : montant en euros de la première échéance (à multiplier par 100 ici)
     * - period : nombre de jours entre 2 paiements
     * - email : email du responsable
     * - responsableId : référence du responsable
     * - nom : nom du responsable
     * - prénom : prénom du responsable
     * - eleveIds : tableau des eleveId, référence des élèves concernés par ce paiement
     * Renvoie la chaine de données à transmettre.
     * 
     * @param array $params  
     * 
     * @return string
     */
    public function prepareAppel($params);
}