<?php
/**
 * Interface pour TablePlugin
 * 
 * (voir SbmPaiement\Plugin\SystemPay\Db\Table\TablePlugin par exemple)
 * 
 * @project sbm
 * @package SbmPaiement/Plugin
 * @filesource TablePluginInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmPaiement\Plugin;

use Zend\Db\Sql\Where;

interface TablePluginInterface
{

    /**
     * Renvoie la propriété id_name de la table.
     *
     * @return string
     */
    public function getIdName();

    /**
     * Renvoie un tableau de définition des éléments du formulaire de critères pour la page
     * paiement/liste
     *
     * @return array
     */
    public function criteres();

    /**
     * Modifie le Where créé par la méthode initListe() afin de tenir compte des formats imposés
     * par la plateforme de paiement.
     * Cette méthode peut être vide si rien n'est nécessaire.
     *
     * @param Where $where
     */
    public function adapteWhere(Where &$where);
}