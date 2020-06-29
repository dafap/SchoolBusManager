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
 * @date 29 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db;

use SbmBase\Model\StdLib;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

abstract class CommandSql
{

    /**
     * Renvoie une requete SELECT pour créer une VIEW
     *
     * @param \Zend\Db\Adapter\Adapter $dbadapter
     * @param array $structure
     *            (description de la structure dans
     *            SbmInstallation/config/db_design/README.txt)
     * @return string
     */
    public static function getSelectForCreateView($dbadapter, $prefix, $structure)
    {
        self::isValidDbDesignStructureView($structure); // lance une exception si la
                                                        // structure n'est pas bonne
        $sql = new Select();
        $table = StdLib::entityName($structure['from']['table'],
            array_key_exists('type', $structure['from']) ? $structure['from']['type'] : 'table',
            $prefix);
        $table = array_key_exists('alias', $structure['from']) ? [
            $structure['from']['alias'] => $table
        ] : $table;
        $sql->from($table);
        if (strtolower(StdLib::getParam('quantifier', $structure,'') == 'distinct')) {
            $sql->quantifier(Select::QUANTIFIER_DISTINCT);
        }
        $sql->columns(
            self::getColumnsFromDbDesignFields($structure['fields']));
        if (array_key_exists('join', $structure)) {
            foreach ($structure['join'] as $join) {
                $table = StdLib::entityName($join['table'],
                    array_key_exists('type', $join) ? $join['type'] : 'table', $prefix);
                $table = array_key_exists('alias', $join) ? [
                    $join['alias'] => $table
                ] : $table;
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
        if (array_key_exists('where', $structure)) {
            $sql->where(self::getWhere($structure['where']));
        }
        if (array_key_exists('group', $structure)) {
            $fields = [];
            foreach ($structure['group'] as $field) {
                $fields[] = $field['table'] . '.' . $field['field'];
            }
            $sql->group($fields);
        }
        if (\array_key_exists('order', $structure)) {
            $sql->order($structure['order']);
        }
        // die($sql->getSqlString($dbadapter->getPlatform()));
        return $sql->getSqlString($dbadapter->getPlatform());
    }

    /**
     * Lance une exception si la structure n'est pas bonne
     *
     * @param array $structure
     *            (description de la structure dans
     *            SbmInstallation/config/db_design/README.txt)
     * @throws Exception\OutOfBoundsException
     *
     * @return boolean
     */
    public static function isValidDbDesignStructureView($structure)
    {
        $ok = is_array($structure) && array_key_exists('fields', $structure) &&
            is_array($structure['fields']) && array_key_exists('from', $structure) &&
            array_key_exists('table', $structure['from']);
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
            $message .= html_entity_decode(strip_tags(ob_get_clean()));
            throw new Exception\OutOfBoundsException($message);
        }
    }

    /**
     * Vérifie la validité de la structure définissant une liste de join
     *
     * @param array $join
     *            (description de la structure dans
     *            SbmInstallation/config/db_design/README.txt)
     * @return boolean
     */
    private static function isValidDbDesignJoin($joins)
    {
        $ok = false;
        $j = 1;
        foreach ($joins as $join) {
            $ok = array_key_exists('table', $join) && array_key_exists('relation', $join);
            if ($ok && array_key_exists('fields', $join)) {
                $ok = self::isValidDbDesignFields($join['fields']);
            }
            if (! $ok) {
                break;
            }
            $j ++;
        }
        return $ok;
    }

    /**
     * Vérifie la validité de la structure définisant une liste de champs
     *
     * @param array $fields
     *            (description de la structure de ce tableau dans
     *            SbmInstallation/config/db_design/README.txt)
     * @return boolean
     */
    private static function isValidDbDesignFields($fields)
    {
        if (empty($fields)) {
            return true;
        }
        $ok = false;
        foreach ($fields as $field) {
            $ok = array_key_exists('field', $field) ||
                (array_key_exists('expression', $field) &&
                array_key_exists('alias', $field));
            if (! $ok) {
                break;
            }
        }
        return $ok;
    }

    private static function getColumnsFromDbDesignFields($fields)
    {
        $result = [];
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

    /**
     *
     * @param array $array
     *
     * @throws Exception\DomainException
     *
     * @return \Zend\Db\Sql\Where|\Zend\Db\Sql\Predicate\Predicate
     */
    public static function getWhere($array)
    {
        $where = new Where();
        $result = $where;
        foreach ($array as $arguments) {
            $predicate = array_shift($arguments);
            switch ($predicate) {
                case 'and':
                    $result = $result->and;
                case 'or':
                    $result = $result->or;
                case 'nest':
                case '(':
                    $result = $result->nest();
                case 'unnest':
                case ')':
                    $result = $result->unnest();
                case 'between':
                    list ($identifier, $minValue, $maxValue) = $arguments;
                    $result = $result->between($identifier, $minValue, $maxValue);
                    break;
                case 'expression':
                    list ($expression, $parameters) = $arguments;
                    $result = $result->expression($expression, $parameters);
                    break;
                case 'in':
                    list ($identifier, $valueSet) = $arguments;
                    $result = $result->in($identifier, $valueSet);
                    break;
                case 'isnotnull':
                    list ($identifier) = $arguments;
                    $result = $result->isNotNull($identifier);
                    break;
                case 'isnull':
                    list ($identifier) = $arguments;
                    $result = $result->isNotNull($identifier);
                    break;
                case 'like':
                    list ($identifier, $like) = $arguments;
                    $result = $result->like($identifier, $like);
                    break;
                case 'literal':
                    list ($literal) = $arguments;
                    $result = $result->literal($literal);
                    break;
                case 'notin':
                    list ($identifier, $valueSet) = $arguments;
                    $result = $result->notIn($identifier);
                    break;
                case 'notlike':
                    list ($identifier, $notLike) = $arguments;
                    $result = $result->notLike($identifier, $notLike);
                    break;
                case 'equalto':
                case '=':
                    if (count($arguments) == 4) {
                        list ($left, $right, $leftType, $rightType) = $arguments;
                        $result = $result->equalTo($left, $right, $leftType, $rightType);
                    } else {
                        list ($left, $right) = $arguments;
                        $result = $result->equalTo($left, $right);
                    }
                    break;
                case 'notequalto':
                case '<>':
                case '!=':
                    if (count($arguments) == 4) {
                        list ($left, $right, $leftType, $rightType) = $arguments;
                        $result = $result->notEqualTo($left, $right, $leftType, $rightType);
                    } else {
                        list ($left, $right) = $arguments;
                        $result = $result->notEqualTo($left, $right);
                    }
                    break;
                case 'lessthan':
                case '<':
                    if (count($arguments) == 4) {
                        list ($left, $right, $leftType, $rightType) = $arguments;
                        $result = $result->lessThan($left, $right, $leftType, $rightType);
                    } else {
                        list ($left, $right) = $arguments;
                        $result = $result->lessThan($left, $right);
                    }
                    break;
                case 'lessthanorequalto':
                case '<=':
                    if (count($arguments) == 4) {
                        list ($left, $right, $leftType, $rightType) = $arguments;
                        $result = $result->lessThanOrEqualTo($left, $right, $leftType,
                            $rightType);
                    } else {
                        list ($left, $right) = $arguments;
                        $result = $result->lessThanOrEqualTo($left, $right);
                    }
                    break;
                case 'greaterthan':
                case '>':
                    if (count($arguments) == 4) {
                        list ($left, $right, $leftType, $rightType) = $arguments;
                        $result = $result->greaterThan($left, $right, $leftType,
                            $rightType);
                    } else {
                        list ($left, $right) = $arguments;
                        $result = $result->greaterThan($left, $right);
                    }
                    break;
                case 'greaterthanorequalto':
                case '>=':
                    if (count($arguments) == 4) {
                        list ($left, $right, $leftType, $rightType) = $arguments;
                        $result = $result->greaterThanOrEqualTo($left, $right, $leftType,
                            $rightType);
                    } else {
                        list ($left, $right) = $arguments;
                        $result = $result->greaterThanOrEqualTo($left, $right);
                    }
                    break;
                default:
                    $msg = sprintf(
                        " : La clé `%s` du tableau passé en paramètre est inconnue.",
                        $predicate);
                    throw new Exception\DomainException(__METHOD__ . $msg);
                    break;
            }
        }
        return $result;
    }
}