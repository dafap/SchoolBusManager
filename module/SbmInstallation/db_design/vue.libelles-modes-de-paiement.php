<?php
/**
 * Structure de la vue `libelles-modes-de-paiement`
 *
 * Requête donnant la liste des (code, libelle) pour les modes de paiement ouverts (nature = ModeDePaiement ; ouvert = 1)
 * 
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.libelles-modes-de-paiement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 janv. 2015
 * @version 2015-1
 */

return array(
    'name' => 'libelles-modes-de-paiement',
    'type' => 'vue',
    'drop' => true, 
    'edit_entity' => true,
    'structure'=> array(
        'fields' => array(
            array('field' => 'code',),
            array('field' => 'libelle',),
        ),
        'from' => array(
            'table' => 'libelles', // obligatoire mais peut être une vue
            'type' => 'system', // optionnel, 'table' par défaut
            'alias' => 'mode', // optionnel
        ),
        'where' => array(
             array('literal', 'nature="ModeDePaiement"'),
             array('literal', 'ouvert=1')
        ),
    ),
);