<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource TablePluginInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 avr. 2015
 * @version 2015-1
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
     * Renvoie un tableau de définition des éléments du formulaire de critères pour la page paiement/liste
     * 
     * @return array
     */
    public function criteres();
    
    /**
     * Modifie le Where créé par la méthode initListe() afin de tenir compte des formats imposés par la plateforme de paiement.
     * Cette méthode peut être vide si rien n'est nécessaire.
     * 
     * @param Where $where
     */
    public function adapteWhere(Where &$where);
}