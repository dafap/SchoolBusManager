<?php
/**
 * Classe permettant de traiter le fichier d'extraction de Paybox venant du BackOffice pour effectuer
 * les rattrapages si nécessaires. Outre les rattrapages dans les tables, elle renvoie un tableau de
 * compte-rendu permettant d'établir un bilan.
 *
 * Le fichier reçu est au format MS Excel
 * La première ligne contient des entêtes qui sont vérifiés à partir de la configuration
 * indiquée dans Plugin/PayBox/config.
 *
 * Pour chaque ligne du fichier:
 * - vérifier qu'il s'agit d'une écriture demandée par SBM (table des appels)
 * - ne garder que les Type de transaction == DEBIT (les autres sont des remboursements depuis le backoffice)
 * - déterminer s'il s'agit d'un abonnement, d'une résiliation ou d'un paiement
 * - pour les abonnements : le Statut de la transaction est 'Acceptée' et le mois de la transaction n'est pas
 *                          le mois du paiement (datePaiement)
 *      § si l'abonnement est déjà inscrit dans la table on a rien à faire. On passe à la ligne suivante ;
 *      § sinon on enregistre la ligne dans la table paybox, on prépare les data et on lance les évènements
 *      'paiementOK' et 'scolariteOK' permettant de mettre à jour les tables paiements et scolarites.
 * - pour les résiliations : Statut de la transaction est 'Refusée' et le mois de la transaction n'est pas
 *                          le mois du paiement (datePaiement)
 *      § si l'abonnement est inscrit dans la table on doit enregistrer l'incident en annulant les paiements
 *        et en décochant le paiement dans la table scolarites
 *      § sinon il n'y a rien à faire car l'abonnement n'avait pas été enregistré.
 * - pour les paiements : le Statut de la transaction est 'Acceptée' et cela n'a pas été traité précédemment
 *      § si le paiement est déjà inscrit dans la table on a rien à faire. On passe à la ligne suivante ;
 *      § sinon on enregistre la ligne dans la table paybox, on prépare les data et on lance les évènements
 *      'paiementOK' et 'scolariteOK' permettant de mettre à jour les tables paiements et scolarites.
 *
 * @project sbm
 * @package SbmPaiement/Model/Plugin/PayBox
 * @filesource Rapprochement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 août 2020
 * @version 2020-2.6.0
 */
namespace SbmPaiement\Plugin\PayBox;

class Rapprochement extends AbstractNotification
{

    /**
     * Configuration du fichier d'extraction du backoffice de paybox : 'columns' : lettres
     * de colonne, 'head' : intitulés de colonne (voir PayBox/config/paybox.config.php)
     *
     * @see paybox.config.php
     * @var array
     */
    private $extraction;


}