<?php
/**
 * Structure de la vue `paiements`
 *
 * La requête reprend toutes les colonnes de la table `paiements` ainsi que les colonnes `responsable`, `caisse` et `modeDePaiement` 
 * 
 * @project sbm
 * @package package_name
 * @filesource vue.paiements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 janv. 2015
 * @version 2015-1
 */
return array(
    'name' => 'paiements',
    'type' => 'vue',
    'drop' => true,
    'edit_entity' => true,
    'structure' => array(
        'fields' => array(
            array(
                'field' => 'paiementId'
            ),
            array(
                'field' => 'dateDepot'
            ),
            array(
                'field' => 'datePaiement'
            ),
            array(
                'field' => 'dateValeur'
            ),
            array(
                'field' => 'responsableId'
            ),
            array(
                'field' => 'anneeScolaire'
            ),
            array(
                'field' => 'exercice'
            ),
            array(
                'field' => 'montant'
            ),
            array(
                'field' => 'codeModeDePaiement'
            ),
            array(
                'field' => 'codeCaisse'
            ),
            array(
                'field' => 'banque'
            ),
            array(
                'field' => 'titulaire'
            ),
            array(
                'field' => 'reference'
            )
        ),
        'from' => array(
            'table' => 'paiements', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'pai'
        ), // optionnel
        'join' => array(
            array(
                'table' => 'responsables',
                'type' => 'table',
                'alias' => 'res',
                'relation' => 'pai.responsableId = res.responsableId',
                'fields' => array(
                    array(
                        'expression' => array(
                            'value' => "CONCAT(res.nom, ' ', res.prenom)",
                            'type' => 'varchar(61)'
                        ),
                        'alias' => 'responsable'
                    )
                )
            ),
            array(
                'table' => 'libelles-caisses',
                'type' => 'vue',
                'alias' => 'cai',
                'relation' => 'pai.codeCaisse = cai.code',
                'fields' => array(
                    array(
                        'field' => 'libelle',
                        'alias' => 'caisse'
                    )
                )
            ),
            array(
                'table' => 'libelles-modes-de-paiement',
                'type' => 'vue',
                'alias' => 'mod',
                'relation' => 'pai.codeModeDePaiement = mod.code',
                'fields' => array(
                    array(
                        'field' => 'libelle',
                        'alias' => 'modeDePaiement'
                    )
                )
            ),
        )
    )
);