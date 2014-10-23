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
 * @version 2014-2 (23 oct. 2014)
 */
namespace SbmInstallation\Model;

use Zend\Debug\Debug;
use SbmInstallation\Model\Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use SbmCommun\Model\StdLib;
use SbmCommun\Model\Db\CommandSql;
use SbmCommun;

class CreateTables
{

    const BAD_DESIGN = 200;

    const BAD_DROP = 201;

    const BAD_CREATE = 202;

    const BAD_TRIGGER = 203;

    const DB_DESIGN_PATH = '/../../../db_design';

    /**
     * Prend la valeur 'table', 'system' ou 'vue'
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
     * Préfixe des tables, system et des vues
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
     * Permet de savoir quelle est la cause d'une erreur
     *
     * @var string array
     */
    private $err_msg;

    /**
     *
     * @var array (voir SbmInstallation/config/db_design/README.txt)
     */
    protected $db_design = array();

    public function __construct($dbconfig, Adapter $dbadapter)
    {
        $this->database = $dbconfig['database'];
        $this->prefix = $dbconfig['prefix'];
        $this->definer = $dbconfig['definer'];
        $this->dbadapter = $dbadapter;
        $this->dir();
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
            $insert = $sql->insert(StdLib::entityName($entity['name'], $entity['type'], $this->prefix));
            $insert->values($data);
            $sqlString = $sql->getSqlStringForSqlObject($insert);
            $results[] = $this->dbadapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        }
        return $results;
    }

    protected function alterTable($entityName, $entityStructure, $entityType)
    {
        // à faire
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
     *            'table', 'system' ou 'vue' afin de pouvoir créer les tables temporaires de même nom que les vues avant de créer les vues
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

    /**
     * Renvoie un tableau décrivant la structure de la vue.
     * Pour le moment, les vues sont basées sur des tables et des vues (pas des system)
     *
     * @param string $viewName            
     * @return multitype:multitype:string multitype:string unknown
     */
    protected function analyseView($viewName)
    {
        $structure = array();
        $filename = 'vue.' . $viewName . '.php';
        $entity = require (__DIR__ . '/../../../db_design/' . $filename);
        $viewStructure = $entity['structure'];
        SbmCommun\Model\Db\CommandSql::isValidDbDesignStructureView($viewStructure); // lance une exception si la structure n'est pas bonne
        $table = $viewStructure['from']['table'];
        if ($viewStructure['from']['type'] == 'table') {
            $filename = 'table.' . $table . '.php';
            $entity = require (__DIR__ . '/../../../db_design/' . $filename);
            $fields_table = $entity['structure']['fields'];
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
                        $value = $field['expression']['type'];
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
                    $filename = 'table.' . $table . '.php';
                    $entity = require (__DIR__ . '/../../../db_design/' . $filename);
                    $join_structure = $entity['structure']['fields'];
                } else {
                    // il s'agit d'une vue
                    $join_structure = $this->analyseView($table);
                }
                foreach ($join['fields'] as $field) {
                    if (array_key_exists('alias', $field)) {
                        $nom_field = $field['alias'];
                        if (array_key_exists('expression', $field)) {
                            $value = $field['expression']['type'];
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
        return array(
            'fields' => $structure
        );
    }

    /**
     *
     * @param string $entityName            
     * @param array $entityStructure            
     * @throws Exception
     * @return Ambigous <\Zend\Db\Adapter\Driver\StatementInterface, \Zend\Db\ResultSet\Zend\Db\ResultSet, \Zend\Db\Adapter\Driver\ResultInterface, \Zend\Db\ResultSet\Zend\Db\ResultSetInterface>
     */
    protected function createTmpTableForView($entityName, $entityStructure)
    {
        $structure = $this->analyseView($entityName);
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
     * Création de l'entité (table, system ou vue)
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
        if ($entity['type'] == 'table' || $entity['type'] == 'system') {
            if ($this->existsEntity($entity['name'], $entity['type'])) {
                $command = $this->alterTable($entity['name'], $entity['structure'], $entity['type']);
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

    protected function createTriggers($entity)
    {
        $prefix = $this->prefix;
        $table_name = StdLib::entityName($entity['name'], $entity['type'], $prefix);
        $result = '';
        foreach ($entity['triggers'] as $name => $structure) {
            $command = "DROP TRIGGER IF EXISTS `$name`";
            try {
                $r = $this->dbadapter->query($command, Adapter::QUERY_MODE_EXECUTE);
                $result .= "$command -> OK\n";
            } catch (\PDOException $e) {
                $message = "Impossible d'exécuter la commande $command.\n" . $e->getMessage();
                throw new Exception($message, self::BAD_TRIGGER);
            }
            
            $moment = $structure['moment'];
            if (! in_array(strtoupper($moment), array(
                'BEFORE',
                'AFTER'
            ))) {
                $message = "Définition incorrecte du moment dans la structure du trigger %s.\nLes valeurs possibles sont BEFORE et AFTER";
                throw new Exception($message, self::BAD_TRIGGER);
            }
            
            $evenement = $structure['evenement'];
            if (! in_array(strtoupper($evenement), array(
                'INSERT',
                'UPDATE',
                'DELETE'
            ))) {
                $message = "Définition incorrecte de l'évènement dans la structure du trigger %s.\nLes valeurs possibles sont INSERT, UPDATE et DELETE";
                throw new Exception($message, self::BAD_TRIGGER);
            }
            
            $definition = trim($structure['definition'], ';');
            $definition = preg_replace_callback('/%(table|system)\((.*)\)%/i', function ($matches) use($prefix)
            {
                return StdLib::entityName($matches[2], $matches[1], $prefix);
            }, $definition);
            
            $command = <<<EOT
CREATE TRIGGER `$name` 
 $moment $evenement ON `$table_name`
 FOR EACH ROW BEGIN
 $definition;
 END;
EOT;
            try {
                $r = $this->dbadapter->query($command, Adapter::QUERY_MODE_EXECUTE);
                $result .= "CREATE TRIGGER `$name` $moment $evenement ON `$table_name` -> OK\n";
            } catch (\PDOException $e) {
                $message = "Impossible d'exécuter la commande :\n$command.\n" . $e->getMessage();
                throw new Exception($message, self::BAD_TRIGGER);
            }
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
        
        if ($entityType == 'vue') {
            
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
        // pour la gestion des erreurs
        ob_start();
        var_dump($entity);
        $dump = ob_end_clean();
        
        if (! is_array($entity)) {
            $this->err_msg = "L'entité fournie n'est pas un tableau.\n$dump";
            return false;
        }
        foreach (array(
            'name',
            'type',
            'drop'
        ) as $key) {
            if (! array_key_exists($key, $entity)) {
                $this->err_msg = "$key n'est pas une clé du tableau décrivant l'entité.\n$dump";
                return false;
            }
        }
        if (! in_array($entity['type'], array(
            'table',
            'system',
            'vue'
        ))) {
            $this->err_msg = "Le type de l'entité est incorrect.";
            return false;
        }
        if (! is_string($entity['name'])) {
            $this->err_msg = "Le nom de l'entité doit être une chaine de caractères.";
            return false;
        }
        if (! is_bool($entity['drop'])) {
            $this->err_msg = "La clé `drop` doit être un booléen.";
            return false;
        }
        if (array_key_exists('edit_entity', $entity) && ! is_bool($entity['edit_entity'])) {
            $this->err_msg = "La clé `edit_entity` doit être un booléen.";
            return false;
        }
        if (array_key_exists('add_data', $entity) && ! is_bool($entity['add_data'])) {
            $this->err_msg = "La clé `add_data` doit être un booléen.";
            return false;
        }
        
        if (array_key_exists('structure', $entity) && ! is_array($entity['structure'])) {
            $this->err_msg = "La clé `structure` doit être un tableau.";
            return false;
        }
        if (array_key_exists('data', $entity) && ! is_array($entity['data'])) {
            $this->err_msg = "La clé `data` doit être un tableau.";
            return false;
        }
        return true;
    }

    /**
     * Méthode pour créer les entités demandées
     *
     * @return array
     */
    public function run()
    {
        $result = array();
        $entities = array( // il s'agit de la véritable nature de l'entité dans MySQL. Un type 'system' est une table.
            'table',
            'vue'
        );
        foreach ($entities as $type_entity) {
            foreach ($this->db_design as $filename) {
                $entity = require (__DIR__ . '/../../../db_design/' . $filename);
                if (! $this->isEntity($entity)) {
                    $message = "Le fichier $filename est incorrect.\n" . $this->err_msg;
                    throw new Exception($message, self::BAD_DESIGN);
                }
                if ($entity['type'] == $type_entity || ($entity['type'] == 'system' && $type_entity == 'table')) {
                    if ($entity['drop']) {
                        $result[] = $this->dropEntity($entity['name'], $entity['type']);
                    }
                    if (array_key_exists('structure', $entity)) {
                        $result[] = $this->createOrAlterEntity($entity);
                    }
                    if (($type_entity == 'table') && array_key_exists('triggers', $entity)) {
                        $result[] = $this->createTriggers($entity);
                    }
                    if (($type_entity == 'table') && array_key_exists('data', $entity)) {
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

    private function dir()
    {
        $dossier = opendir(__DIR__ . self::DB_DESIGN_PATH);
        while ($f = readdir($dossier)) {
            $p = explode('.', $f);
            if (! is_dir($f) && count($p) == 3 && $p[2] == 'php' && ($p[0] == 'table' || $p[0] == 'system' || $p[0] == 'vue')) {
                $this->db_design[] = $f;
            }
        }
        closedir($dossier);
    }

    public function voir()
    {
        $keys = array(
            'name',
            'type',
            'drop',
            'edit_entity',
            'add_data',
            'data'
        );
        $result = array();
        foreach ($this->db_design as $filename) {
            $buffer = file(__DIR__ . self::DB_DESIGN_PATH . '/' . $filename);
            $row = array_fill_keys($keys, '');
            foreach ($buffer as $ligne) {
                preg_match("@^\s*'(.*)'\s*=>\s*(?:include __DIR__ \. '/)?'?([^',]*)'?,?\s*(?://.*)*$@i", $ligne, $parts);
                if (count($parts) == 3) {
                    $key = $parts[1];
                    $value = $parts[2];
                    if (in_array($key, $keys) && $row[$key] == '') {
                        if ($value == 'false') {
                            $value = 'Non';
                        } elseif ($value == 'true') {
                            $value = 'Oui';
                        }
                        
                        $row[$key] = $value;
                    }
                }
            }
            $result[] = $row;
        }
        return $result;
    }
}