<?php
/**
 * Classe abstraite pour création de table et de vues
 *
 *
 * @project sbm
 * @package module/SbmInstallation/src/SbmInstallation/Model
 * @filesource AbstractCreate.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 janv. 2014
 * @version 2014-1
 */
namespace SbmInstallation\Model;

use Zend\Debug\Debug;
use SbmInstallation\Model\Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use SbmCommun\Model\StdLib;
use SbmCommun\Model\Db\CommandSql;
use SbmCommun;

class Create
{

    const BAD_DESIGN = 200;

    const BAD_DROP = 201;

    const BAD_CREATE = 202;

    /**
     * Prend la valeur 'table' ou 'vue'
     *
     * @var string
     */
    protected $type_entity;

    /**
     * Nom de la base de donnée
     *
     * @var string
     */
    protected $database;

    /**
     * Préfixe des tables et des vues
     *
     * @var string
     */
    protected $prefix;

    /**
     * le DEFINER pour les vues
     *
     * @var string
     */
    protected $definer;

    /**
     *
     * @var Adapter
     */
    protected $dbadapter;

    /**
     *
     * @var array (voir SbmInstallation/config/db_design/README.txt)
     */
    protected $db_design;

    public function __construct($dbconfig, Adapter $dbadapter, $db_design)
    {
        $this->database = $dbconfig['database'];
        $this->prefix = $dbconfig['prefix'];
        $this->definer = $dbconfig['definer'];
        $this->dbadapter = $dbadapter;
        $this->db_design = $db_design;
    }

    protected function addData($entity)
    {
        if (array_key_exists('add_data', $entity) && ! $entity['add_data']) {
            return sprintf('Pas d\'ajout de données dans la table `%s`.', $entity['name']);
        }
        $results = array();
        $sql = new Sql($this->dbadapter);
        foreach ($entity['data'] as $data) {
            set_time_limit(20);
            $insert = $sql->insert(StdLib::entityName($entity['name'], 'table', $this->prefix));
            $insert->values($data);
            $sqlString = $sql->getSqlStringForSqlObject($insert);
            $results[] = $this->dbadapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        }
        
        return $results;
    }

    protected function alterTable($entityName, $entityStructure)
    {
        return '';
    }

    /**
     * Renvoie la commande SQL de création d'une table
     *
     * @param string $entityName
     *            nom de la table ou de la vue sans les préfixes
     * @param array $entityStructure
     *            structure décrivant la table (voir README.txt)
     * @param string $entityType
     *            'table' ou 'vue' afin de pouvoir créer les tables temporaires de même nom que les vues avant de créer les vues
     *            
     * @return string
     */
    protected function createTable($entityName, $entityStructure, $entityType)
    {
        if (! array_key_exists('fields', $entityStructure) || ! is_array($entityStructure['fields']) || empty($entityStructure['fields'])) {
            return;
        }
        $command = 'CREATE TABLE IF NOT EXISTS `' . StdLib::entityName($entityName, $entityType, $this->prefix) . '` (';
        $sep = "\n";
        foreach ($entityStructure['fields'] as $key => $value) {
            $command .= $sep . sprintf('`%s` %s', $key, $value);
            $sep = ",\n";
        }
        if (array_key_exists('primary_key', $entityStructure) && is_array($entityStructure['primary_key'])) {
            $command .= $sep . 'PRIMARY KEY (`' . implode('`,`', $entityStructure['primary_key']) . '`)';
        }
        if (array_key_exists('keys', $entityStructure) && is_array($entityStructure['keys']) && ! empty($entityStructure['keys'])) {
            foreach ($entityStructure['keys'] as $key => $value) {
                $unique = array_key_exists('unique', $value) && $value['unique'] ? 'UNIQUE ' : '';
                $command .= $sep . $unique . "KEY `$key` (`" . implode('`,`', $value['fields']) . '`)';
            }
        }
        $command .= "\n)";
        if (array_key_exists('engine', $entityStructure)) {
            $command .= ' ENGINE=' . $entityStructure['engine'];
        }
        if (array_key_exists('charset', $entityStructure)) {
            $command .= ' DEFAULT CHARSET=' . $entityStructure['charset'];
        }
        if (array_key_exists('collate', $entityStructure)) {
            $command .= ' COLLATE=' . $entityStructure['collate'];
        }
        return $command . ';';
    }

    protected function analyseView($viewName)
    {
        $structure = array();
        $viewStructure = $this->db_design['vue.' . $viewName]['structure'];
        $table = $viewStructure['from']['table'];
        if ($viewStructure['from']['type'] == 'table') {
            $fields_table = $this->db_design['table.' . $table]['structure']['fields'];
            foreach ($viewStructure['fields'] as $field) {
                // chercher la définition de ce champ dans cette table
                if (array_key_exists('alias', $field)) {
                    // créer un champ du nom de cet alias
                    $nom_field = $field['alias'];
                    if (array_key_exists('expression', $field)) {
                        $value = $field['expression'];
                    } else {
                        $value = $fields_table[$field['field']];
                    }
                } else {
                    // créer un champ du nom de field
                    $nom_field = $field['field'];
                    $value = $fields_table[$field['field']];
                }
                $structure[$nom_field] = trim(str_replace('AUTO_INCREMENT', '', $value)); 
            }
        } else {
            // il s'agit d'une vue qu'il faut aussi analyser
            $tmp_structure = $this->analyseView($table);
            foreach ($viewStructure['fields'] as $field) {
                if (array_key_exists('alias', $field)) {
                    $nom_field = $field['alias'];
                    if (array_key_exists('expression', $field)) {
                        $value = $field['expression'];
                    } else {
                        $value = $tmp_structure[$field['field']];
                    }
                } else {
                    $nom_field = $field['field'];
                    $value = $tmp_structure[$field['field']];
                }
                $structure[$nom_field] = $value;
            }
        }
        // analyse des 'join'
        if (array_key_exists('join', $viewStructure)) {
            foreach ($viewStructure['join'] as $join) {
                $table = $join['table'];
                if ($join['type'] == 'table') {
                    $join_structure = $this->db_design['table.' . $table]['structure']['fields'];
                } else {
                    // il s'agit d'une vue
                    $join_structure = $this->analyseView($table);
                }
                foreach ($join['fields'] as $field) {
                    if (array_key_exists('alias', $field)) {
                        $nom_field = $field['alias'];
                        if (array_key_exists('expression', $field)) {
                            $value = $field['expression'];
                        } else {
                            $value = $join_structure[$field['field']];
                        }
                    } else {
                        $nom_field = $field['field'];
                        $value = $join_structure[$field['field']];
                    }
                    $structure[$nom_field] = trim(str_replace('AUTO_INCREMENT', '', $value));
                }
            }
        }
        return array('fields' => $structure);
    }

    protected function createTmpTableForView($entityName, $entityStructure)
    {
        SbmCommun\Model\Db\CommandSql::isValidDbDesignStructureView($entityStructure); // lance une exception si la structure n'est pas bonne
        $structure = $this->analyseView($entityName, $entityStructure);
        $command = $this->createTable($entityName, $structure, 'vue');
        try {
            if (! empty($command)) {
                $result = $this->dbadapter->query($command, Adapter::QUERY_MODE_EXECUTE);
            }
        } catch (\PDOException $e) {
            $message = "Impossible d'exécuter la commande $command.\n" . $e->getMessage();
            throw new Exception($message, self::BAD_CREATE);
        }
        return $result;
    }

    /**
     * Renvoie la commande SQL de création d'une vue
     *
     * @param string $entityName            
     * @param array $entityStructure            
     *
     * @return string
     */
    protected function createView($entityName, $entityStructure)
    {
        return sprintf("CREATE OR REPLACE DEFINER=%s SQL SECURITY DEFINER VIEW `%s` AS %s;", $this->definer, StdLib::entityName($entityName, 'vue', $this->prefix), CommandSql::getSelectForCreateView($this->dbadapter, $this->prefix, $entityStructure));
    }

    /**
     * Création de l'entité (table ou vue)
     *
     * @param string $entityName            
     * @param string $entityType            
     * @param array $entityStructure            
     */
    protected function createOrAlterEntity($entity)
    {
        if (array_key_exists('edit_entity', $entity) && ! $entity['edit_entity']) {
            return sprintf('Pas de modification de structure pour la table `%s`.', $entity['name']);
        }
        if ($entity['type'] == 'table') {
            if ($this->existsEntity($entity['name'], $entity['type'])) {
                $command = $this->alterTable($entity['name'], $entity['structure']);
            } else {
                $command = $this->createTable($entity['name'], $entity['structure'], $entity['type']);
            }
        } else {
            $command = $this->createView($entity['name'], $entity['structure']);
        }
        try {
            if (! empty($command)) {
                $result = $this->dbadapter->query($command, Adapter::QUERY_MODE_EXECUTE);
            }
        } catch (\PDOException $e) {
            $message = "Impossible d'exécuter la commande $command.\n" . $e->getMessage();
            throw new Exception($message, self::BAD_CREATE);
        }
        return $result;
    }

    /**
     * Fait un DROP TABLE IF EXISTS ou un DROP VIEW IF EXISTS sur l'entité nommée.
     * (pour les vues, on fait un DROP TABLE avant un DROP VIEW au cas où il aurait fallu créé une table fictive pour les relations et les autres vues)
     *
     * @param string $entityName            
     * @param string $entityType            
     *
     * @return Zend\Db\Adapter\Driver\StatementInterface Zend\Db\ResultSet\ResultSet
     */
    protected function dropEntity($entityName, $entityType)
    {
        $command = sprintf('DROP TABLE IF EXISTS `%s`;', StdLib::entityName($entityName, $entityType, $this->prefix));
        try {
            $result = $this->dbadapter->query($command, Adapter::QUERY_MODE_EXECUTE);
        } catch (\PDOException $e) {
            $message = "Impossible d'exécuter la commande $command.\n" . $e->getMessage();
            throw new Exception($message, self::BAD_DROP);
        }
        
        if ($entityType = 'vue') {
            $command = sprintf('DROP VIEW IF EXISTS `%s`;', StdLib::entityName($entityName, $entityType, $this->prefix));
            try {
                $result = $this->dbadapter->query($command, Adapter::QUERY_MODE_EXECUTE);
            } catch (\PDOException $e) {
                $message = "Impossible d'exécuter la commande $command.\n" . $e->getMessage();
                throw new Exception($message, self::BAD_DROP);
            }
        }
        return $result;
    }

    /**
     * Renvoie un booléen indiquant si la table ou la vue existe dans la base ou non
     *
     * @param string $entityName            
     * @param string $entityType            
     *
     * @return boolean
     */
    protected function existsEntity($entityName, $entityType)
    {
        $command = sprintf("SHOW TABLES LIKE '%s'", StdLib::entityName($entityName, $entityType, $this->prefix));
        return $this->dbadapter->query($command, Adapter::QUERY_MODE_EXECUTE)->count() == 1;
    }

    /**
     * Vérifie la structure d'un fichier de configuration
     *
     * @param array $entity
     *            Contenu d'un fichier de configuration
     *            
     * @return boolean
     */
    protected function isEntity($entity)
    {
        if (is_array($entity)) {
            $ok = array_key_exists('name', $entity) & array_key_exists('type', $entity) & array_key_exists('drop', $entity);
            if ($ok) {
                $ok = in_array($entity['type'], array(
                    'table',
                    'vue'
                )) && is_string($entity['name']) && is_bool($entity['drop']);
                if ($ok && array_key_exists('structure', $entity)) {
                    $ok = is_array($entity['structure']);
                }
                if ($ok && array_key_exists('data', $entity)) {
                    $ok = is_array($entity['data']);
                }
            }
            return $ok;
        } else {
            return false;
        }
    }

    /**
     * Méthode pour créer les entités demandées
     *
     * @return array
     */
    public function run()
    {
        $result = array();
        $entities = array(
            'table',
            'vue'
        );
        foreach ($entities as $type_entity) {
            foreach ($this->db_design as $filename => $entity) {
                if (! $this->isEntity($entity)) {
                    $message = "Le fichier $filename est incorrect.";
                    throw new Exception($message, self::BAD_DESIGN);
                }
                if ($entity['type'] == $type_entity) {
                    if ($entity['drop']) {
                        $result[] = $this->dropEntity($entity['name'], $entity['type']);
                    }
                    if (array_key_exists('structure', $entity)) {
                        $result[] = $this->createOrAlterEntity($entity);
                    }
                    if ($type_entity == 'table' && array_key_exists('data', $entity)) {
                        $result[] = $this->addData($entity);
                    }
                } elseif ($type_entity == 'table') {
                    $result[] = 'Vue : ' . $entity['name'];
                    // c'est dans le premier passage et on a à faire à une vue. 
                    // Création de la table temporaire remplaçant la vue et évitant les blocages de création des vues.
                    $result[] = $this->dropEntity($entity['name'], $entity['type']);
                    $result[] = $this->createTmpTableForView($entity['name'], $entity['structure']);
                }
            }
        }
        return $result;
    }
}