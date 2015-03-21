<?php
use Zend\Db\Sql\Select;
/**
 * Structure de la vue `eleves`
 *
 * Découpage en `eleves`, `scolarites`, `affectations` et `responsables`
 *
 * @project sbm
 * 
 * @package SbmInstallation/db_design
 * @filesource vue.eleves.php
 *             @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 *         @date 3 nov. 2014
 * @version 2014-1
 */

return array(
    'name' => 'eleves',
    'type' => 'vue',
    'drop' => true,
    'edit_entity' => true,
    'structure' => array(
        'fields' => array(
            array(
                'field' => 'eleveId'
            ),
            array(
                'field' => 'selection'
            ),
            array(
                'field' => 'dateCreation'
            ),
            array(
                'field' => 'dateModification'
            ),
            array(
                'field' => 'nom'
            ),
            array(
                'field' => 'nomSA'
            ),
            array(
                'field' => 'prenom'
            ),
            array(
                'field' => 'prenomSA'
            ),
            array(
                'field' => 'dateN'
            ),
            array(
                'field' => 'numero'
            ),
            array(
                'field' => 'responsable1Id'
            ),
            array(
                'field' => 'x1'
            ),
            array(
                'field' => 'y1'
            ),
            array(
                'field' => 'responsable2Id'
            ),
            array(
                'field' => 'x2'
            ),
            array(
                'field' => 'y2'
            ),
            array(
                'field' => 'responsableFId'
            ),
            array(
                'field' => 'note'
            )
        ),
        'from' => array(
            'table' => 'eleves',
            'type' => 'table',
            'alias' => 'ele'
        ),
        'join' => array(
            array(
                'table' => 'responsables', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'r1', // optionnel
                'relation' => 'ele.responsable1Id = r1.responsableId', // obligatoire
                'fields' => array(
                    array(
                        'expression' => array(
                            'value' => "CONCAT(r1.nom, ' ', r1.prenom)",
                            'type' => 'varchar(61)'
                        ),
                        'alias' => 'responsable1NomPrenom'
                    )
                )
            ),
            array(
                'table' => 'responsables',
                'type' => 'table',
                'alias' => 'r2',
                'relation' => 'ele.responsable2Id = r2.responsableId', // obligatoire case when r2.responsableId IS NULL then '' else concat(r2.nom, ' ', r2.prenom) end
                'fields' => array(
                    array(
                        'expression' => array(
                            'value' => "CASE WHEN r2.responsableId IS NULL THEN '' ELSE CONCAT(r2.nom, ' ', r2.prenom) END",
                            'type' => 'varchar(61)'
                        ),
                        'alias' => 'responsable2NomPrenom'
                    )
                ),
                'jointure' => Select::JOIN_LEFT
            ),
            array(
                'table' => 'responsables',
                'type' => 'table',
                'alias' => 'rf',
                'relation' => 'ele.responsableFId = rf.responsableId', // obligatoire
                'fields' => array(
                    array(
                        'expression' => array(
                            'value' => "CASE WHEN rf.responsableId IS NULL THEN CONCAT(r1.nom, ' ', r1.prenom) ELSE CONCAT(rf.nom, ' ', rf.prenom) END",
                            'type' => 'varchar(61)'
                        ),
                        'alias' => 'responsableFNomPrenom'
                    )
                ),
                'jointure' => Select::JOIN_LEFT
            )
        )
    )
);