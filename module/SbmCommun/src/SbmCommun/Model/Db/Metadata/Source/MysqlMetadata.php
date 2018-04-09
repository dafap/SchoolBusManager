<?php
/**
 * Extension de la classe Zend\Db\Metadata\Source\MysqlMetadata pour avoir une information sur les colonnes auto_increment
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Metadata/Source
 * @filesource MysqlMetadata.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Metadata\Source;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Metadata\Source\MysqlMetadata as ZendMysqlMetadata;

class MysqlMetadata extends ZendMysqlMetadata
{

    protected function loadColumnData($table, $schema)
    {
        if (isset($this->data['columns'][$schema][$table])) {
            return;
        }
        $this->prepareDataHierarchy('columns', $schema, $table);
        $p = $this->adapter->getPlatform();
        
        $isColumns = [
            [
                'C',
                'ORDINAL_POSITION'
            ],
            [
                'C',
                'COLUMN_DEFAULT'
            ],
            [
                'C',
                'IS_NULLABLE'
            ],
            [
                'C',
                'DATA_TYPE'
            ],
            [
                'C',
                'CHARACTER_MAXIMUM_LENGTH'
            ],
            [
                'C',
                'CHARACTER_OCTET_LENGTH'
            ],
            [
                'C',
                'NUMERIC_PRECISION'
            ],
            [
                'C',
                'NUMERIC_SCALE'
            ],
            [
                'C',
                'COLUMN_NAME'
            ],
            [
                'C',
                'COLUMN_TYPE'
            ],
            [
                'C',
                'EXTRA'
            ]
        ];
        
        array_walk($isColumns, 
            function (&$c) use($p) {
                $c = $p->quoteIdentifierChain($c);
            });
        
        $sql = 'SELECT ' . implode(', ', $isColumns) . ' FROM ' . $p->quoteIdentifierChain(
            [
                'INFORMATION_SCHEMA',
                'TABLES'
            ]) . 'T' . ' INNER JOIN ' . $p->quoteIdentifierChain(
            [
                'INFORMATION_SCHEMA',
                'COLUMNS'
            ]) . 'C' . ' ON ' . $p->quoteIdentifierChain(
            [
                'T',
                'TABLE_SCHEMA'
            ]) . '  = ' . $p->quoteIdentifierChain(
            [
                'C',
                'TABLE_SCHEMA'
            ]) . ' AND ' . $p->quoteIdentifierChain(
            [
                'T',
                'TABLE_NAME'
            ]) . '  = ' . $p->quoteIdentifierChain(
            [
                'C',
                'TABLE_NAME'
            ]) . ' WHERE ' . $p->quoteIdentifierChain(
            [
                'T',
                'TABLE_TYPE'
            ]) . ' IN (\'BASE TABLE\', \'VIEW\')' . ' AND ' . $p->quoteIdentifierChain(
            [
                'T',
                'TABLE_NAME'
            ]) . '  = ' . $p->quoteTrustedValue($table);
        
        if ($schema != self::DEFAULT_SCHEMA) {
            $sql .= ' AND ' . $p->quoteIdentifierChain(
                [
                    'T',
                    'TABLE_SCHEMA'
                ]) . ' = ' . $p->quoteTrustedValue($schema);
        } else {
            $sql .= ' AND ' . $p->quoteIdentifierChain(
                [
                    'T',
                    'TABLE_SCHEMA'
                ]) . ' != \'INFORMATION_SCHEMA\'';
        }
        
        $results = $this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
        $columns = [];
        foreach ($results->toArray() as $row) {
            $erratas = [];
            $matches = [];
            if (preg_match('/^(?:enum|set)\((.+)\)$/i', $row['COLUMN_TYPE'], $matches)) {
                $permittedValues = $matches[1];
                if (preg_match_all("/\\s*'((?:[^']++|'')*+)'\\s*(?:,|\$)/", 
                    $permittedValues, $matches, PREG_PATTERN_ORDER)) {
                    $permittedValues = str_replace("''", "'", $matches[1]);
                } else {
                    $permittedValues = [
                        $permittedValues
                    ];
                }
                $erratas['permitted_values'] = $permittedValues;
            }
            if (empty($row['EXTRA'])) {
                $erratas['auto_increment'] = false;
            } elseif ($row['EXTRA'] == 'auto_increment') {
                $erratas['auto_increment'] = true;
            } else {
                $erratas['auto_increment'] = false;
            }
            $columns[$row['COLUMN_NAME']] = [
                'ordinal_position' => $row['ORDINAL_POSITION'],
                'column_default' => $row['COLUMN_DEFAULT'],
                'is_nullable' => ('YES' == $row['IS_NULLABLE']),
                'data_type' => $row['DATA_TYPE'],
                'character_maximum_length' => $row['CHARACTER_MAXIMUM_LENGTH'],
                'character_octet_length' => $row['CHARACTER_OCTET_LENGTH'],
                'numeric_precision' => $row['NUMERIC_PRECISION'],
                'numeric_scale' => $row['NUMERIC_SCALE'],
                'numeric_unsigned' => (false !== strpos($row['COLUMN_TYPE'], 'unsigned')),
                'erratas' => $erratas
            ];
        }
        
        $this->data['columns'][$schema][$table] = $columns;
    }
}
