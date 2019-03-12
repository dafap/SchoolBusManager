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
 * @date 7 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmInstallation\Model;

use SbmBase\Model\StdLib;
use SbmCommun\Model\Db\CommandSql;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class CreateTables
{

    const BAD_DESIGN = 200;

    const BAD_DROP = 201;

    const BAD_CREATE = 202;

    const BAD_TRIGGER = 203;

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
    // protected $database;

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
     * Files ordonnées de traitement des create et des datas
     * et liste des index nécessaires pour les 'foreign key' dans les tables référencées
     *
     * @var array
     */
    protected $queue = [];

    /**
     * Constructeur
     *
     * @param array $dbconfig
     *            tableau de configuration de la base de données pour récupérer le 'prefix' et le
     *            'definer'
     * @param Adapter $dbadapter
     */
    public function __construct($dbconfig, Adapter $dbadapter)
    {
        // $this->database = $dbconfig['database'];
        $this->prefix = $dbconfig['prefix'];
        $this->definer = $dbconfig['definer'];
        $this->dbadapter = $dbadapter;
        $this->createQueue('data');
    }

    /**
     * Renvoie le chemin absolu du dossier db_design ou du fichier $file contenu dans ce dossier
     *
     * @param string $file
     *
     * @return string
     */
    public function dbDesignPath($file = null)
    {
        if (empty($file)) {
            return StdLib::findParentPath(__DIR__, 'db_design');
        }
        return StdLib::concatPath(StdLib::findParentPath(__DIR__, 'db_design'), $file);
    }

    /**
     * Crée la queue de traitement des tables.
     * Cette queue définit
     * - la liste des fichiers de définition dans db_design associés à leur type
     * - l'ordre de traitement des tables 'system', 'table', 'vue'
     * - les index nécessaires aux 'foreign key' dans les tables référencées
     * - l'ordre de traitement des 'data'
     */
    private function createQueue()
    {
        $this->queue = [
            'db_design' => [],
            'system' => [],
            'table' => [],
            'vue' => [],
            'foreign key' => [],
            'data' => []
        ];
        foreach ($this->dir() as $item) {
            $this->insereQueue($item);
        }
    }

    /**
     * Insère la clé $item à sa place dans la structure de la queue
     * Tient compte des 'foreign key' pour trouver la place dans la queue.
     *
     * @param string $item
     *            nom du fichier de définition de l'entité (sans son chemin)
     */
    private function insereQueue($include_file)
    {
        if (! array_key_exists($include_file, $this->queue['db_design'])) {
            $filename = $this->dbDesignPath($include_file);
            $def = include ($filename);
            if (! $this->isEntity($def)) {
                $message = "Le fichier $include_file est incorrect.\n" . $this->err_msg .
                    "\n";
                $message .= "\n-----------------------------\n";
                $message .= "$filename\n";
                $message .= file_get_contents($filename) . "\n";
                $message .= "\n-----------------------------\n";
                throw new Exception($message, self::BAD_DESIGN);
            }
            if ($def['type'] != 'vue' && array_key_exists('foreign key', $def['structure'])) {
                foreach ($def['structure']['foreign key'] as $fk) {
                    $precedent = $def['type'] . '.' . $fk['references']['table'] . '.php';
                    $this->insereQueue($precedent);
                }
            }
            $this->addQueue($include_file, $def);
        }
    }

    /**
     * Ajoute la clé $item dans la structure de la queue.
     * A la fin,
     * - le tableau $this->queue['system'] donne l'ordre de création des tables system
     * - le tableau $this->queue['table'] donne l'ordre de création des tables
     * - le tableau $this->queue['vue'] donne l'ordre de création des vues
     * - le tableau $this->queue['foreign key'] donne par type (system | table) pour chaque table
     * référencée, la liste des clés à créer si elles n'existent pas
     * - le tableau $this->queue['data'] donne pour chaque fichier de donnée à inclure (indexé par
     * son nom complet), le nom de la table et son type
     *
     * @param string $include_file
     *            nom du fichier de définition de l'entité (sans son chemin)
     * @param array $def
     *            tableau de définition de l'entité (table, table system, vue)
     */
    private function addQueue($include_file, $def)
    {
        $this->queue['db_design'][$include_file] = $def['type'];
        $this->queue[$def['type']][$include_file] = $def;
        if (array_key_exists('foreign key', $def['structure'])) {
            foreach ($def['structure']['foreign key'] as $fk) {
                $table = $fk['references']['table'];
                $fields = $fk['references']['fields'];
                if (! array_key_exists($def['type'], $this->queue['foreign key']) ||
                    ! array_key_exists($table, $this->queue['foreign key'][$def['type']]) ||
                    ! in_array($fields, $this->queue['foreign key'][$def['type']][$table])) {
                    $this->queue['foreign key'][$def['type']][$table][] = $fields;
                }
            }
        }
        if (! empty($def['add_data']) && array_key_exists('data', $def)) {
            // dans la structure de $def, remplacer la sous-structure 'data' par une chaine 'nom
            // complet du fichier à inclure'
            $this->queue['data'][$def['data']] = [
                $def['name'],
                $def['type']
            ]; // réduire la clé à $array['data']
        }
    }

    /**
     * Ajoute les données dans la table
     *
     * @param string $file_data
     *            fichier de définition des données
     * @param array $properties
     *            0 => nom_court de la table, 1 => type de la table
     *
     * @return multitype:Ambigous <\Zend\Db\Adapter\Driver\StatementInterface,
     *         \Zend\Db\ResultSet\Zend\Db\ResultSet>
     */
    protected function addData($file_data, $properties)
    {
        $donnees = include ($file_data);
        $results = [];
        $sql = new Sql($this->dbadapter);
        foreach ($donnees as $data) {
            set_time_limit(20);
            $insert = $sql->insert(
                StdLib::entityName($properties[0], $properties[1], $this->prefix));
            $insert->values($data);
            // $sqlString = $sql->getSqlStringForSqlObject($insert); obsolete
            $sqlString = $sql->buildSqlString($insert);
            try {
                $results[] = $this->dbadapter->query($sqlString,
                    Adapter::QUERY_MODE_EXECUTE);
            } catch (\Exception $e) {
                $msg = __METHOD__ . "\n" . $sqlString;
                throw new Exception($msg, 999, $e);
            }
        }
        return $results;
    }

    /**
     * A FAIRE
     *
     * @param string $entityName
     * @param array $entityStructure
     * @param string $entityType
     * @return string
     */
    protected function alterTable($entityName, $entityStructure, $entityType)
    {
        // @todo: alterTable à faire
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
     *            'table', 'system' ou 'vue' afin de pouvoir créer les tables temporaires de même
     *            nom que les vues avant de créer les vues
     *
     * @return string
     */
    protected function createTable($entityName, $entityStructure, $entityType)
    {
        if (! array_key_exists('fields', $entityStructure) ||
            ! is_array($entityStructure['fields']) || empty($entityStructure['fields'])) {
            return;
        }
        $command = 'CREATE TABLE IF NOT EXISTS `' .
            StdLib::entityName($entityName, $entityType, $this->prefix) . '` (';
        $sep = "\n";
        foreach ($entityStructure['fields'] as $key => $value) {
            $command .= $sep . sprintf('`%s` %s', $key, $value);
            $sep = ",\n";
        }
        if (array_key_exists('primary_key', $entityStructure) &&
            is_array($entityStructure['primary_key'])) {
            $pk_str = implode('`,`', $entityStructure['primary_key']);
            $command .= $sep . "PRIMARY KEY (`$pk_str`)";
        }
        if (array_key_exists('keys', $entityStructure) &&
            is_array($entityStructure['keys']) && ! empty($entityStructure['keys'])) {
            $keys_str = [];
            foreach ($entityStructure['keys'] as $key => $value) {
                $unique = array_key_exists('unique', $value) && $value['unique'] ? 'UNIQUE ' : '';
                $tmp = implode('`,`', $value['fields']);
                $command .= $sep . $unique . "KEY `$key` (`$tmp`)";
                $keys_str[] = $tmp;
            }
            unset($tmp);
        }
        // ajout des index nécessaires pour les foreign key référençant cette table
        if (StdLib::array_keys_exists([
            'foreign key',
            $entityType,
            $entityName
        ], $this->queue)) {
            $numero = 1;
            foreach ($this->queue['foreign key'][$entityType][$entityName] as $fk) {
                $fk_str = implode('`,`', $fk);
                // vérifier si la pk est suffisante
                if (isset($pk_str) && mb_strcut($pk_str, 0, mb_strlen($fk_str)) == $fk_str)
                    continue;
                $trouve = false;
                if (isset($keys_str)) {
                    // vérifier les autres keys
                    foreach ($keys_str as $key) {
                        $trouve |= mb_strcut($key, 0, mb_strlen($fk_str)) == $fk_str;
                    }
                    if ($trouve)
                        continue;
                }
                // la fk n'a pas été trouvé, il faut la créer
                $key = "dafap_$entityName" . "_fk$numero";
                $command .= $sep . "KEY `$key` (`$fk_str`)";
            }
        }
        if (array_key_exists('foreign key', $entityStructure)) {
            foreach ($entityStructure['foreign key'] as $fk) {
                $command .= $sep . 'FOREIGN KEY (`' . implode('`,`', (array) $fk['key']) .
                    '`) REFERENCES `' .
                    StdLib::entityName($fk['references']['table'], $entityType,
                        $this->prefix) . '`(`' .
                    implode('`,`', $fk['references']['fields']) . '`)';
                if (array_key_exists('on', $fk['references'])) {
                    if (array_key_exists('update', $fk['references']['on'])) {
                        $command .= ' ON UPDATE ' .
                            strtoupper($fk['references']['on']['update']);
                    }
                    if (array_key_exists('delete', $fk['references']['on'])) {
                        $command .= ' ON DELETE ' .
                            strtoupper($fk['references']['on']['delete']);
                    }
                }
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
        $structure = [];
        $filename = 'vue.' . $viewName . '.php';
        $entity = require ($this->dbDesignPath($filename));
        $viewStructure = $entity['structure'];
        // lance une exception si la structure n'est pas bonne
        CommandSql::isValidDbDesignStructureView($viewStructure);
        $table = $viewStructure['from']['table'];
        if ($viewStructure['from']['type'] == 'table' ||
            $viewStructure['from']['type'] == 'system') {
            $filename = $viewStructure['from']['type'] . '.' . $table . '.php';
            $entity = require ($this->dbDesignPath($filename));
            $fields_table = $entity['structure']['fields'];
            foreach ($viewStructure['fields'] as $field) {
                // chercher la définition de ce champ dans cette table
                if (array_key_exists('alias', $field)) {
                    // créer un champ du nom de cet alias
                    $nom_field = $field['alias'];
                    if (array_key_exists('expression', $field)) {
                        $value = $field['expression']['type'];
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
                if ($join['type'] == 'table' || $join['type'] == 'system') {
                    $filename = $join['type'] . '.' . $table . '.php';
                    $entity = require ($this->dbDesignPath($filename));
                    $join_structure = $entity['structure']['fields'];
                } else {
                    // il s'agit d'une vue
                    $join_structure = $this->analyseView($table);
                    $join_structure = $join_structure['fields'];
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
                    $structure[$nom_field] = trim(
                        str_replace('AUTO_INCREMENT', '', $value));
                }
            }
        }
        return [
            'fields' => $structure
        ];
    }

    /**
     *
     * @param string $entityName
     * @param array $entityStructure
     * @throws Exception
     * @return \Zend\Db\Adapter\Driver\StatementInterface|\Zend\Db\ResultSet\ResultSet|\Zend\Db\Adapter\Driver\ResultInterface|\Zend\Db\ResultSet\ResultSetInterface
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
        return sprintf(
            "CREATE OR REPLACE DEFINER=%s SQL SECURITY DEFINER VIEW `%s` AS %s;",
            $this->definer, StdLib::entityName($entityName, 'vue', $this->prefix),
            CommandSql::getSelectForCreateView($this->dbadapter, $this->prefix,
                $entityStructure));
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
            return sprintf('Pas de modification de structure pour la table `%s`.',
                $entity['name']);
        }
        if ($entity['type'] == 'table' || $entity['type'] == 'system') {
            if ($this->existsEntity($entity['name'], $entity['type'])) {
                $command = $this->alterTable($entity['name'], $entity['structure'],
                    $entity['type']);
            } else {
                $command = $this->createTable($entity['name'], $entity['structure'],
                    $entity['type']);
            }
        } else {
            $command = $this->createView($entity['name'], $entity['structure']);
        }
        try {
            if (! empty($command)) {
                $result = $this->dbadapter->query($command, Adapter::QUERY_MODE_EXECUTE);
            } else {
                return null;
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
                unset($r);
                $result .= "$command -> OK\n";
            } catch (\PDOException $e) {
                $message = "Impossible d'exécuter la commande $command.\n" .
                    $e->getMessage();
                throw new Exception($message, self::BAD_TRIGGER);
            }

            $moment = $structure['moment'];
            if (! in_array(strtoupper($moment), [
                'BEFORE',
                'AFTER'
            ])) {
                $message = "Définition incorrecte du moment dans la structure du trigger %s.\nLes valeurs possibles sont BEFORE et AFTER";
                throw new Exception($message, self::BAD_TRIGGER);
            }

            $evenement = $structure['evenement'];
            if (! in_array(strtoupper($evenement), [
                'INSERT',
                'UPDATE',
                'DELETE'
            ])) {
                $message = "Définition incorrecte de l'évènement dans la structure du trigger %s.\nLes valeurs possibles sont INSERT, UPDATE et DELETE";
                throw new Exception($message, self::BAD_TRIGGER);
            }

            $definition = trim($structure['definition'], ';');
            $definition = preg_replace_callback('/%(table|system)\((.*)\)%/i',
                function ($matches) use ($prefix) {
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
                $message = "Impossible d'exécuter la commande :\n$command.\n" .
                    $e->getMessage();
                throw new Exception($message, self::BAD_TRIGGER);
            }
        }
        return $result;
    }

    /**
     * Fait un DROP TABLE IF EXISTS ou un DROP VIEW IF EXISTS sur l'entité nommée.
     * (pour les vues, on fait un DROP TABLE avant un DROP VIEW au cas où il aurait fallu créé une
     * table fictive pour les relations et les autres vues)
     *
     * @param string $entityName
     * @param string $entityType
     *
     * @return \Zend\Db\Adapter\Driver\StatementInterface|\Zend\Db\ResultSet\ResultSet
     */
    protected function dropEntity($entityName, $entityType)
    {
        $command = sprintf('DROP TABLE IF EXISTS `%s`;',
            StdLib::entityName($entityName, $entityType, $this->prefix));
        try {
            $result = $this->dbadapter->query($command, Adapter::QUERY_MODE_EXECUTE);
        } catch (\PDOException $e) {
            $message = "Impossible d'exécuter la commande $command.\n" . $e->getMessage();
            throw new Exception($message, self::BAD_DROP);
        }

        if ($entityType == 'vue') {

            $command = sprintf('DROP VIEW IF EXISTS `%s`;',
                StdLib::entityName($entityName, $entityType, $this->prefix));
            try {
                $result = $this->dbadapter->query($command, Adapter::QUERY_MODE_EXECUTE);
            } catch (\PDOException $e) {
                $message = "Impossible d'exécuter la commande $command.\n" .
                    $e->getMessage();
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
        $command = sprintf("SHOW TABLES LIKE '%s'",
            StdLib::entityName($entityName, $entityType, $this->prefix));
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
        foreach ([
            'name',
            'type',
            'drop'
        ] as $key) {
            if (! array_key_exists($key, $entity)) {
                $this->err_msg = "$key n'est pas une clé du tableau décrivant l'entité.\n$dump";
                return false;
            }
        }
        if (! in_array($entity['type'], [
            'table',
            'system',
            'vue'
        ])) {
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

        if (array_key_exists('structure', $entity)) {
            if (! is_array($entity['structure'])) {
                $this->err_msg = "La clé `structure` doit être un tableau.";
                return false;
            }
            if (! array_key_exists('fields', $entity['structure']) ||
                ! is_array($entity['structure']['fields'])) {
                $this->err_msg = "Dans la partie `structure` la clé `fields` est nécessaire et doit être un tableau.";
                return false;
            }
            if (array_key_exists('primary_key', $entity['structure']) &&
                ! is_array($entity['structure']['primary_key'])) {
                $this->err_msg = "Dans la partie `structure` la clé `primary_key` doit être un tableau.";
                return false;
            }
            if (array_key_exists('keys', $entity['structure'])) {
                if (! is_array($entity['structure']['keys'])) {
                    $this->err_msg = "Dans la partie `structure` la clé `keys` doit être un tableau.";
                    return false;
                }
                foreach ($entity['structure']['keys'] as $key) {
                    if (! is_array($key) || ! array_key_exists('fields', $key)) {
                        $this->err_msg = "Dans la partie `structure` chaque clé de `keys` doit être un tableau qui doit posséder la clé `fields`.";
                        return false;
                    }
                    if (! is_array($key['fields'])) {
                        $this->err_msg = "Dans la partie `structure` chaque clé de `keys` doit présenter une clé `fields` qui est un tableau.";
                        return false;
                    }
                }
            }

            if (array_key_exists('foreign key', $entity['structure'])) {
                if (! is_array($entity['structure']['foreign key'])) {
                    $this->err_msg = "La clé `foreign key` de la partie `structure` doit être un tableau.";
                    return false;
                }
                $ok = true;
                $i = 1;
                foreach ($entity['structure']['foreign key'] as $fk) {
                    $ok &= array_key_exists('key', $fk) &&
                        (is_string($fk['key']) || is_array($fk['key']));
                    $ok &= array_key_exists('references', $fk) &&
                        is_array($fk['references']);
                    $ok &= array_key_exists('table', $fk['references']) &&
                        is_string($fk['references']['table']);
                    $ok &= array_key_exists('fields', $fk['references']) &&
                        is_array($fk['references']['fields']);
                    if (array_key_exists('on', $fk['references'])) {
                        $permis = [
                            'CASCADE',
                            'SET NULL',
                            'NO ACTION',
                            'RESTRICT'
                        ];
                        if (array_key_exists('update', $fk['references']['on'])) {
                            $ok &= in_array(strtoupper($fk['references']['on']['update']),
                                $permis);
                        }
                        if (array_key_exists('delete', $fk['references']['on'])) {
                            $ok &= in_array(strtoupper($fk['references']['on']['delete']),
                                $permis);
                        }
                    }
                    if (! $ok) {
                        $this->err_msg = "Erreur dans la définition de la 'FOREIGN KEY' n° $i.";
                        return false;
                    }
                    $i ++;
                }
            }
        }
        if (array_key_exists('data', $entity) && ! is_string($entity['data'])) {
            $this->err_msg = "La clé `data` doit être une chaine.";
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
        $result = [];
        // création des tables (tables et tables systèmes)
        foreach ([
            'system',
            'table'
        ] as $type) {
            // liste drop des tables 'system'
            $keys = array_keys($this->queue[$type]);
            while (! is_null($element = array_pop($keys))) {
                if ($this->queue[$type][$element]['drop']) {
                    $result[] = $this->dropEntity($this->queue[$type][$element]['name'],
                        $type);
                }
            }
            // création des tables
            foreach ($this->queue[$type] as $filename => $entity) {
                if (array_key_exists('structure', $entity)) {
                    $result[] = $this->createOrAlterEntity($entity);
                }
                if (array_key_exists('triggers', $entity)) {
                    $result[] = $this->createTriggers($entity);
                }
            }
        }
        // création des vues. On crée d'abord des tables puis on les remplace par des vues
        foreach ([
            'table',
            'vue'
        ] as $type_mysql) {
            foreach ($this->queue['vue'] as $entity) {
                if ($entity['drop']) {
                    $result[] = $this->dropEntity($entity['name'], $entity['type']);
                }
                if ($type_mysql == 'table') {
                    // création de tables temporaires qui remplacent les vues pour éviter les
                    // blocages lors de leur création.
                    $result[] = 'Vue : ' . $entity['name'];
                    $result[] = $this->createTmpTableForView($entity['name'],
                        $entity['structure']);
                } elseif (array_key_exists('structure', $entity)) {
                    $result[] = $this->createOrAlterEntity($entity);
                }
            }
        }
        // peuplement des tables
        foreach ($this->queue['data'] as $filename => $properties) {
            $result[] = $this->addData($filename, $properties);
        }
        // @todo: exploiter la liste des messages d'erreur
        return $result;
    }

    private function dir()
    {
        $result = [];
        $dossier = opendir($this->dbDesignPath());
        while ($f = readdir($dossier)) {
            $p = explode('.', $f);
            if (! is_dir($f) && count($p) == 3 && $p[2] == 'php' &&
                ($p[0] == 'table' || $p[0] == 'system' || $p[0] == 'vue')) {
                $result[] = $f;
            }
        }
        closedir($dossier);
        asort($result);
        return $result;
    }

    public function voir()
    {
        $keys = [
            'name',
            'type',
            'drop',
            'edit_entity',
            'add_data',
            'data'
        ];
        $result = [];
        foreach ($this->dir() as $filename) {
            $buffer = file($this->dbDesignPath($filename));
            $row = array_fill_keys($keys, '');
            $parts = null;
            foreach ($buffer as $ligne) {
                preg_match(
                    "@^\s*'(.*)'\s*=>\s*(?:include __DIR__ \. '/)?'?([^',]*)'?,?\s*(?://.*)*$@i",
                    $ligne, $parts);
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