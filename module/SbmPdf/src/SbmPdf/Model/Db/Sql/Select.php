<?php
/**
 * Surcharge de la classe Zend\Db\Sql\Select pour exécuter une requête complexe basée sur une chaine SQL
 *
 * On a ajouté la méthode setRecordSource qui permet de passer une chaine SQL complète comme source de donnée.
 * 
 * Lorsque la chaine SQL recordSource est donnée (constructeur ou setter), elle remplace la table indiquée dans FROM. 
 * En définitive, la méthode from() sera ignorée si la méthode recordSource() est appelée.
 * Par contre, si on n'appelle pas cette nouvelle méthode, la classe aura un comportement normal (à condition de ne
 * rien passer au constructeur).
 * 
 * Ensuite, pour exécuter la requête, il faut pratiquer de la façon suivante :
 *   $sqlString = $select->getSqlString($dbAdapter->getPlatform());
 *   $rowset = $dbAdapter->query($sqlString, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
 * 
 * @project sbm
 * @package SbmPdf/Model/Db/Sql
 * @filesource Select.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmPdf\Model\Db\Sql;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Select as ZendSelect;
use Zend\Crypt\PublicKey\Rsa\PublicKey;

class Select extends ZendSelect
{

    private $recordSource;

    /**
     * On passe le recordSource (sql string) par le constructeur.
     * On ne peut plus passer la table.
     *
     * @param string $recordSource            
     */
    public function __construct($recordSource = null)
    {
        parent::__construct();
        $this->recordSource = $recordSource;
    }

    /**
     * On peut aussi le passer par le setter
     *
     * @param string $recordSource            
     * @return \SbmPdf\Model\Db\Sql\Select
     */
    public function setRecordSource($recordSource)
    {
        $this->recordSource = $recordSource;
        return $this;
    }

    /**
     * Surcharge de la méthode
     *
     * (non-PHPdoc)
     *
     * @see \Zend\Db\Sql\Select::processSelect()
     */
    protected function processSelect(PlatformInterface $platform, 
        DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        $expr = 1;
        if (empty($this->recordSource)) {
            list ($table, $fromTable) = parent::resolveTable($this->table, $platform, 
                $driver, $parameterContainer);
        } else {
            list ($table, $fromTable) = $this->resolveTable($this->recordSource, 
                $platform);
        }
        // process table columns
        $columns = [];
        foreach ($this->columns as $columnIndexOrAs => $column) {
            if ($column === self::SQL_STAR) {
                $columns[] = [
                    $fromTable . self::SQL_STAR
                ];
                continue;
            }
            
            $columnName = $this->resolveColumnValue(
                [
                    'column' => $column,
                    'fromTable' => $fromTable,
                    'isIdentifier' => true
                ], $platform, $driver, $parameterContainer, 
                (is_string($columnIndexOrAs) ? $columnIndexOrAs : 'column'));
            // process As portion
            if (is_string($columnIndexOrAs)) {
                $columnAs = $platform->quoteIdentifier($columnIndexOrAs);
            } elseif (stripos($columnName, ' as ') === false) {
                $columnAs = (is_string($column)) ? $platform->quoteIdentifier($column) : 'Expression' .
                     $expr ++;
            }
            $columns[] = (isset($columnAs)) ? [
                $columnName,
                $columnAs
            ] : [
                $columnName
            ];
        }
        
        // process join columns
        foreach ($this->joins as $join) {
            $joinName = (is_array($join['name'])) ? key($join['name']) : $join['name'];
            $joinName = parent::resolveTable($joinName, $platform, $driver, 
                $parameterContainer);
            
            foreach ($join['columns'] as $jKey => $jColumn) {
                $jColumns = [];
                $jFromTable = is_scalar($jColumn) ? $joinName .
                     $platform->getIdentifierSeparator() : '';
                $jColumns[] = $this->resolveColumnValue(
                    [
                        'column' => $jColumn,
                        'fromTable' => $jFromTable,
                        'isIdentifier' => true
                    ], $platform, $driver, $parameterContainer, 
                    (is_string($jKey) ? $jKey : 'column'));
                if (is_string($jKey)) {
                    $jColumns[] = $platform->quoteIdentifier($jKey);
                } elseif ($jColumn !== self::SQL_STAR) {
                    $jColumns[] = $platform->quoteIdentifier($jColumn);
                }
                $columns[] = $jColumns;
            }
        }
        
        if ($this->quantifier) {
            $quantifier = ($this->quantifier instanceof ExpressionInterface) ? $this->processExpression(
                $this->quantifier, $platform, $driver, $parameterContainer, 'quantifier') : $this->quantifier;
        }
        
        if (! isset($table)) {
            return [
                $columns
            ];
        } elseif (isset($quantifier)) {
            return [
                $quantifier,
                $columns,
                $table
            ];
        } else {
            return [
                $columns,
                $table
            ];
        }
    }

    protected function resolveTable($table, PlatformInterface $platform, 
        DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        $alias = null;
        
        if (is_array($table)) {
            $alias = key($table);
            $table = current($table);
        } else {
            $alias = 'tmp';
        }
        $fromTable = $platform->quoteIdentifier($alias);
        $table = $this->renderTable($table, $fromTable, false);
        if ($alias) {} else {
            $fromTable = $table;
        }
        
        if ($this->prefixColumnsWithTable && $fromTable) {
            $fromTable .= $platform->getIdentifierSeparator();
        } else {
            $fromTable = '';
        }
        
        return [
            $table,
            $fromTable
        ];
    }

    protected function renderTable($table, $alias = null, $parent = true)
    {
        if ($parent) {
            return parent::renderTable($table, $alias);
        } else {
            if (empty($alias)) {
                $alias = 'tmp';
            }
            return "($table) AS $alias";
        }
    }
}