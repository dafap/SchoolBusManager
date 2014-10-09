<?php
/**
 * Construction des commandes SQL
 *
 *
 * @project sbm
 * @package Sbm/Model/Db
 * @filesource CommandSql.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 janv. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\StdLib;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Ddl;
use Zend\Debug\Debug;
use Zend\Db\Sql\Expression;

abstract class CommandSql
{

    /**
     * Renvoie une requete SELECT pour créer une VIEW
     *
     * @param Adapter $dbadapter            
     * @param array $structure
     *            (description de la structure dans SbmInstallation/config/db_design/README.txt)
     *            
     * @return string
     */
    public static function getSelectForCreateView($dbadapter, $prefix, $structure)
    {
        self::isValidDbDesignStructureView($structure); // lance une exception si la structure n'est pas bonne
        $sql = new Select();
        $table = StdLib::entityName($structure['from']['table'], array_key_exists('type', $structure['from']) ? $structure['from']['type'] : 'table', $prefix);
        $table = array_key_exists('alias', $structure['from']) ? array($structure['from']['alias'] => $table) : $table;
        $sql->from($table);
        $sql->columns(self::getColumnsFromDbDesignFields($structure['fields']));
        if (array_key_exists('join', $structure)) {
            foreach ($structure['join'] as $join) {
                $table = StdLib::entityName($join['table'], array_key_exists('type', $join) ? $join['type'] : 'table', $prefix);
                $table = array_key_exists('alias', $join) ? array($join['alias'] => $table) : $table;
                $on = $join['relation'];
                if (array_key_exists('fields', $join)) {
                    $columns = self::getColumnsFromDbDesignFields($join['fields']);
                } else {
                    $columns = Select::SQL_STAR;
                }
                if (array_key_exists('jointure', $join)) {
                    $sql->join($table, $on, $columns, $join['jointure']);
                } else {
                    $sql->join($table, $on, $columns);
                }
            }
        }
        if (array_key_exists('group', $structure)) {
            $fields = array();
            foreach ($structure['group'] as $field) {
                $fields[] = $field['table'] . '.' . $field['field'];
            }
            $sql->group($fields);
        }
        //die($sql->getSqlString($dbadapter->getPlatform()));
        return $sql->getSqlString($dbadapter->getPlatform());
    }

    /**
     * Lance une exception si la structure n'est pas bonne
     * 
     * @param array $structure
     *            (description de la structure dans SbmInstallation/config/db_design/README.txt)
     * @throws Exception
     * @return boolean
     */
    public static function isValidDbDesignStructureView($structure)
    {
        $ok = is_array($structure) && array_key_exists('fields', $structure) && is_array($structure['fields']) && array_key_exists('from', $structure) && array_key_exists('table', $structure['from']);
        if ($ok) {
            $ok = self::isValidDbDesignFields($structure['fields']);
        }
        if ($ok) {
            if (array_key_exists('join', $structure)) {
                $ok = self::isValidDbDesignJoin($structure['join']);
            }
        }
        if (! $ok) {
            $message = "La structure proposée ne permet pas de créer la requête SELECT.\n";
            ob_start();
            var_dump($structure);
            $message .= ob_get_clean();            
            throw new Exception($message);
        }
    }

    /**
     * Vérifie la validité de la structure définissant une liste de join
     *
     * @param array $join
     *            (description de la structure dans SbmInstallation/config/db_design/README.txt)
     * @return boolean
     */
    private static function isValidDbDesignJoin($joins)
    {
        $ok = false; $j=1;
        foreach ($joins as $join) {
            $ok = array_key_exists('table', $join) && array_key_exists('relation', $join);
            if ($ok && array_key_exists('fields', $join)) {
                $ok = self::isValidDbDesignFields($join['fields']);
            }
            if (! $ok) {
                break;
            }
        }
        return $ok;
    }
    
    /**
     * Vérifie la validité de la structure définisant une liste de champs
     *
     * @param array $fields
     *            (description de la structure de ce tableau dans SbmInstallation/config/db_design/README.txt)
     * @return boolean
     */
    private static function isValidDbDesignFields($fields)
    {
        $ok = false;
        foreach ($fields as $field) {
            $ok = array_key_exists('field', $field) || (array_key_exists('expression', $field) && array_key_exists('alias', $field));
            if (! $ok) {
                break;
            }
        }
        return $ok;
    }
    
    private static function getColumnsFromDbDesignFields($fields)
    {
        $result = array();
        foreach ($fields as $field) {
            if (array_key_exists('field', $field)) {
                if (array_key_exists('alias', $field)) {
                    $result[$field['alias']] = $field['field'];
                } else {
                    $result[] = $field['field'];
                }
            } else { // c'est une expression
                $result[$field['alias']] = new Expression($field['expression']['value']);
            }
        }
        return $result;
    }
}