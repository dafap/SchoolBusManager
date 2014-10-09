<?php
/**
 * Service 'Sbm\Db\DbLib'
 *
 *
 * @project sbm
 * @package SbmCommun
 * @filesource Service/DbLibService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Metadata\Object\TableObject;
use SbmCommun\Model\Db\Metadata\Metadata;
use SbmCommun\Model\StdLib;
use SbmCommun\Model\Db\Exception;

class DbLibService implements FactoryInterface
{

    /**
     * Service manager
     *
     * @var ServiceLocatorInterface
     */
    private $sm;

    /**
     * Adapter permettant d'accéder à la base de données
     *
     * @var Adapter
     */
    private $dbadapter;

    /**
     * Descripteur de la base de donnée
     *
     * @var Metadata
     */
    private $metadata;

    /**
     * Prefixe des noms de tables et de vues
     *
     * @var string
     */
    private $prefix;

    /**
     * Liste des tables
     *
     * @var array
     */
    protected $table_list;

    /**
     * Descripteur des tables
     *
     * @var array
     */
    protected $table_descriptor;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // return new DbLib($serviceLocator);
        $this->sm = $serviceLocator;
        $config = $serviceLocator->get('config');
        $this->prefix = $config['db']['prefix'];
        $this->dbadapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
        $this->metadata = new Metadata($this->dbadapter);
        $this->table_list = $this->metadata->getTableNames(null, true);
        return $this;
    }

    /**
     * Renvoie le nom complet de la table ou de la vue
     *
     * @param string $tableName            
     * @param string $type
     *            Les valeurs permises sont 'table'|'vue'|'system'
     *            
     * @return string
     */
    public function getCanonicName($tableName, $type = 'table')
    {
        $t = empty($this->prefix) ? '' : $this->prefix . '_';
        return $t . substr($type, 0, 1) . "_$tableName";
    }

    /**
     * Indique s'il existe une table ou une vue du nom indiqué dans la base de donnée.
     *
     * @param string $tableName    
     * @param string $type
     *            table|vue|system
     *         
     * @return boolean
     */
    public function existsTable($tableName, $type = 'table')
    {
        return in_array($this->getCanonicName($tableName, $type), $this->table_list);
    }

    /**
     * Renvoie le dbAdapter
     *
     * @return dbAdapter
     */
    public function getDbAdapter()
    {
        return $this->dbadapter;
    }

    /**
     * Renvoie un tableau des tailles de champs pour les champs dont le type à une taille
     * 
     * @param string $tableName
     * @param string $type
     *            table|vue|system
     *            
     * @return array(colName => tailleColonne, ...)
     */
    public function getMaxLengthArray($tableName, $type)
    {
        // initialise DbLib::table_descriptor si nécessaire
        if (! StdLib::array_keys_exists(array(
            $type,
            $tableName,
            'columns'
        ), $this->table_descriptor)) {
            $this->structureTable($tableName, $type);
        }
        
        $result = array();
        foreach ($this->table_descriptor[$type][$tableName]['columns'] as $colName => $descriptor) {
            if (! is_null($descriptor['char_max_len'])) {
                $result[$colName] = $descriptor['char_max_len'];
            }
        }
        return $result;
    }
    
    /**
     * Renvoie un tableau des valeurs par défaut des colonnes pour les colonnes ayant une valeur par défaut
     * 
     * @param string $tableName
     * @param string $type
     *            table|vue|system
     *            
     * @return array(colName => tailleColonne, ...)
     */
    public function getColumnDefaults($tableName, $type)
    {
        // initialise DbLib::table_descriptor si nécessaire
        if (! StdLib::array_keys_exists(array(
            $type,
            $tableName,
            'columns'
        ), $this->table_descriptor)) {
            $this->structureTable($tableName, $type);
        }
        
        $result = array();
        foreach ($this->table_descriptor[$type][$tableName]['columns'] as $colName => $descriptor) {
            if (! is_null($descriptor['column_default'])) {
                $result[$colName] = $descriptor['column_default'];
            }
        }
        return $result;
    }

    /**
     * Renvoie le Metadata
     *
     * @return Metadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Renvoie le préfix du nom des tables et des vues
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Renvoie le ServiceManager
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->sm;
    }

    /**
     * Renvoie la liste des noms de tables de la base de données.
     * C'est le nom complet qui est renvoyé, préfixé et avec l'indicateur de table, system ou de vue.
     *
     * @return the array
     */
    public function getTableNames()
    {
        return $this->table_list;
    }
    
    /**
     * Renvoie un tableau indexé de la forme (nom_réel => nom de l'entité, ...)
     */
    public function getTableList()
    {
        $result = array();
        foreach ($this->table_list as $nom_reel) {
            $type_nom = str_replace($this->prefix . '_', '', $nom_reel);
            list($type, $nom) = explode('_', $type_nom);
            if ($type == 's') continue;
            $type = $type == 't' ? 'table' : ($type == 'v' ? 'données complètes' : 'table système');
            $result[$nom_reel] = implode(' ',array($nom, '(' . $type . ')'));
        }
        asort($result);
        return $result;
    }

    /**
     * Initialise la structure de la table indiquée dans l'attribut DbLib::table_descriptor.
     *
     * @param string $tableName
     * @param string $type
     *            table|vue|system
     *               
     * @throws SbmCommun\Model\Db\Exception
     */
    private function structureTable($tableName, $type = 'table')
    {
        if (! $this->existsTable($tableName, $type)) {
            throw new Exception(sprintf("Il n'y a pas de %s du nom de %s dans la base de données.", $type == 'vue' ?  : $type == 'table' ? : 'table système', $tableName));
        }
        $result = array();
        $table = $this->metadata->getTable($this->getCanonicName($tableName, $type));
        foreach ($table->getColumns() as $column) {
            $result[$column->getName()]['data_type'] = $column->getDataType();
            $result[$column->getName()]['column_default'] = $column->getColumnDefault();
            $result[$column->getName()]['is_nullable'] = $column->getIsNullable();
            $result[$column->getName()]['char_max_len'] = $column->getCharacterMaximumLength();
            $result[$column->getName()]['oct_lax_len'] = $column->getCharacterOctetLength();
            $result[$column->getName()]['numeric_precision'] = $column->getNumericPrecision();
            $result[$column->getName()]['numeric_scale'] = $column->getNumericScale();
            $result[$column->getName()]['is_unsigned'] = $column->getNumericUnsigned();
            $result[$column->getName()]['ordinal_position'] = $column->getOrdinalPosition();
            $result[$column->getName()]['auto_increment'] = $column->getErrata('auto_increment');
        }
        $this->table_descriptor[$type][$tableName]['columns'] = $result;
        // si c'est une table, recherche de sa clé primaire
        if ($type == 'table') {
            $pkc = null;
            foreach ($this->metadata->getConstraints($tableName) as $constraint) {
                if ($constraint->getType() == 'PRIMARY KEY') {
                    $pkc = $constraint;
                    break;
                }
            }
            if (is_null($pkc)) {
                $this->table_descriptor[$type][$tableName]['primary_key'] = null;
            } elseif (count($pkc->getColumns()) == 1) {
                $pkck = $pkc->getColumns();
                $this->table_descriptor[$type][$tableName]['primary_key'] = $pkck[0];
            } else {
                $this->table_descriptor[$type][$tableName]['primary_key'] = $pkc->getColumns();
            }
        }
    }

    /**
     * Renvoie true si la table possède une clé primaire
     *
     * @param string $tableName            
     * @param string $type
     * Prend les valeurs 'table', 'system' ou 'vue'
     *            
     * @return boolean
     */
    public function hasPrimaryKey($tableName, $type)
    {
        if ($type == 'table') {
            // initialise self::table_descriptor si nécessaire
            if (! StdLib::array_keys_exists(array(
                $type,
                $tableName,
                'columns'
            ), $this->table_descriptor)) {
                $this->structureTable($tableName, $type);
            }
            if (StdLib::array_keys_exists(array(
                $type,
                $tableName,
                'primary_key'
            ), $this->table_descriptor)) {
                return ! is_null($this->table_descriptor[$type][$tableName]['primary_key']);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Renvoie la clé primaire ou NULL s'il n'y en a pas
     *
     * @param string $tableName            
     * @param string $type
     * Prend les valeurs 'table', 'system' ou 'vue'
     * 
     * @return mixed: NULL|string|array
     */
    public function getPrimaryKey($tableName, $type)
    {
        if ($this->hasPrimaryKey($tableName, $type)) {
            return $this->table_descriptor[$type][$tableName]['primary_key'];
        } else {
            return null;
        }
    }

    /**
     * Lance une exception s'il n'y a pas de colonne du nom indiqué dans la table indiquée.
     *
     * @param string $columnName            
     * @param string $tableName            
     * @param string $type
     * Prend les valeurs 'table', 'system' ou 'vue'      
     * @param system $method    
     *         
     * @throws SbmCommun\Model\Db\Exception
     */
    private function validColumn($columnName, $tableName, $type = 'table', $method = __METHOD__)
    {
        if (! $this->isColumn($columnName, $tableName, $type)) {
            throw new Exception(sprintf("$method\nLa colonne %s n'existe pas dans la %s %s.", $columnName, $type == 'vue' ?  : 'table', $this->getCanonicName($tableName, $type)));
        }
    }

    /**
     * Indique si la colonne indiquée est auto_increment.
     * Lance une exception si la colonne n'existe pas dans la table indiquée.
     *
     * @param string $columnName            
     * @param string $tableName            
     * @param string $type
     * Prend les valeurs 'table', 'system' ou 'vue'
     * 
     * @return boolean
     */
    public function isAutoIncrement($columnName, $tableName, $type = 'table')
    {
        $this->validColumn($columnName, $tableName, $type, __METHOD__);
        return $this->table_descriptor[$type][$tableName]['columns'][$columnName]['auto_increment'];
    }

    /**
     * Indique s'il existe une colonne du nom indiqué dans la table indiquée.
     *
     * @param string $columnName
     *            Nom de la colonne
     * @param string $tableName
     *            Nom de la table
     * @param string $type
     *            table|vue|system
     *            
     * @return boolean
     */
    public function isColumn($columnName, $tableName, $type = 'table')
    {
        // initialise DbLib::table_descriptor si nécessaire
        if (! StdLib::array_keys_exists(array(
            $type,
            $tableName,
            'columns'
        ), $this->table_descriptor)) {
            $this->structureTable($tableName, $type);
        }
        return array_key_exists($columnName, $this->table_descriptor[$type][$tableName]['columns']);
    }

    /**
     * Indique si la colonne indiquée est de type date (ou time, datetime, timestamp, year).
     * Lance une exception si la colonne n'existe pas dans la table indiquée.
     *
     * @param string $columnName            
     * @param string $tableName            
     * @param string $type         
     *            table|vue|system
     *               
     * @return boolean
     */
    public function isDateTimeColumn($columnName, $tableName, $type = 'table')
    {
        $this->validColumn($columnName, $tableName, $type, __METHOD__);
        return in_array($this->table_descriptor[$type][$tableName]['columns'][$columnName]['data_type'], array(
            'date',
            'datetime',
            'timestamp',
            'time',
            'year'
        ));
    }

    /**
     * Indique si la colonne nommée est de type numérique.
     * Lance une exception si la colonne n'existe pas dans la table indiquée.
     *
     * @param string $columnName            
     * @param string $tableName            
     * @param string $type   
     *            table|vue|system
     *                     
     * @return boolean
     */
    public function isNumericColumn($columnName, $tableName, $type = 'table')
    {
        $this->validColumn($columnName, $tableName, $type, __METHOD__);
        return in_array($this->table_descriptor[$type][$tableName]['columns'][$columnName]['data_type'], array(
            'integer',
            'int',
            'smallint',
            'tinyint',
            'mediumint',
            'bigint',
            'decimal',
            'numeric',
            'float',
            'double',
            'bit'
        ));
    }

    /**
     * Indique si l'entité de nom $tableName est une table.
     * On a vérifié au préalable que cette entité existe.
     *
     * @param string $tableName 
     *            
     * @return boolean
     */
    public function isTable($tableName)
    {
        return $this->metadata->getTable($tableName) instanceof TableObject;
    }
}
