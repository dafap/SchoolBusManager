<?php
/**
 * Interface pour une Plateforme
 *
 * (voir AbstractPlateforme par exemple)
 *
 * @project sbm
 * @package package_name
 * @filesource PlateformeInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 mai 2019
 * @version 2018-2.5.0
 */
namespace SbmPaiement\Plugin;

use Zend\Stdlib\Parameters;

interface PlateformeInterface
{

    /**
     * Renvoie l'URL d'appel de la plateforme qui se trouve en config
     */
    public function getUrl();

    /**
     * Reçoit un tableau de données composé des champs suivants : - montant : en euros (il
     * faut le multiplier par 100 ici) - count : le nombre d'échéances (1 si paiement
     * comptant, n si paiement en n fois) - first : montant en euros de la première
     * échéance (à multiplier par 100 ici) - period : nombre de jours entre 2 paiements -
     * email : email du responsable - responsableId : référence du responsable - nom : nom
     * du responsable - prénom : prénom du responsable - eleveIds : tableau des eleveId,
     * référence des élèves concernés par ce paiement Renvoie la chaine de données à
     * transmettre.
     *
     * @param array $params
     *
     * @return array
     */
    public function prepareAppel($params);

    /**
     * Reçoit un tableau obtenu par la méthode prepareAppel et renvoie une clé unique
     *
     * @param array $params
     *
     * @return string
     */
    public function getUniqueId(array $params);

    /**
     * Si le paiement est valide, il faut préparer les données en vue de l'envoie d'un
     * évènement qui permettra le traitement dans les tables scolarites et paiements.
     * Voici les clés du tableau à fournir dans la propriété paiement : - type : DEBIT ou
     * CREDIT - paiement : tableau contenant - datePaiement - dateValeur - responsableId -
     * anneeScolaire - exercice - montant - codeModeDePaiement - codeCaisse - reference
     * Voici la composition de la propriété scolarites - type : DEBIT ou CREDIT -
     * millesime - eleveIds (tableau des eleveId concernés)
     */
    protected function prepareData();

    /**
     * Cette méthode est appelée par la méthode notification() pour controler la validité
     * de la notification. Cela peut être un contrôle de la signature ou une analyse du
     * contenu de la notification. S'il y a un problème, les propriétés error_no (n°
     * d'erreur) et error_msg (message d'erreur) seront renseignées. Si un traitement est
     * nécessaire sur les données reçues, les données sous leur nouveau format seront
     * placées dans la propriété data de l'objet.
     */
    protected function validNotification(Parameters $data);

    /**
     * Analyse le contenu de la propriété data pour savoir si le paiement a été réalisé
     * par la plateforme. S'il a échoué, les propriétés error_no (n° d'erreur) et
     * error_msg (message d'erreur) seront renseignées. Si un traitement est nécessaire
     * sur les données reçues, les données sous leur nouveau format seront placées dans la
     * propriété data de l'objet, en remplacement des données initiales. En particulier,
     * la référence de la commande sera analysée pour retrouver les éléments qu'elle
     * contient. (En effet, la référence peut dépendre des contraintes de la plateforme)
     */
    protected function validPaiement();
}