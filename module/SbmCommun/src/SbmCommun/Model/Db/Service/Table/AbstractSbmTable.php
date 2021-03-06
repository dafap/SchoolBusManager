<?php
/**
 * Service donnant un descripteur de table
 * 
 * - partie commune à toutes les tables
 * - doit être dérivée pour chaque table selon l'interface TableInterface
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource AbstractSbmTable.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 oct. 2016
 * @version 2016-2.2.1
 */
namespace SbmCommun\Model\Db\Service\Table;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\PredicateInterface;
use Zend\Db\Sql\Predicate\PredicateSet;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use SbmCommun\Model\Db\Service\Table\Exception;
use Zend\Db\Metadata\Object\ColumnObject;

abstract class AbstractSbmTable implements FactoryInterface
{

    /**
     * Descripteur de la base de données
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    protected $db_manager;

    /**
     * Objet d'échange de données
     *
     * @var ObjectDataInterface
     */
    protected $obj_data;

    /**
     * Alias sous lequel ce TableGateway est enregistré dans le ServiceConfig du module
     *
     * @var string
     */
    protected $table_gateway_alias;

    /**
     * objet TableGateway
     *
     * @var \Zend\Db\TableGateway\TableGateway
     */
    protected $table_gateway;

    /**
     * objet Hydrator utilisé pour chaque ligne de résultat des méthodes fetchAll(), paginator(), getRecord(), saveRecord()
     * Cette propriété est définie dans AbstractSbmTableGateway.
     * Sa méthode extract() est utilisée dans saveRecord().
     *
     * @var HydratorInterface
     */
    protected $hydrator = null;

    /**
     * objet Zend\Db\Sql\Select du table_gateway
     *
     * @var Zend\Db\Sql\Select
     */
    protected $obj_select;

    /**
     * Nom de la table (qui est défini dans la table dérivée)
     *
     * @var string
     */
    protected $table_name;

    /**
     * Type : table, vue ou system
     *
     * @var string
     */
    protected $table_type;

    /**
     * Nom du champ id de la table
     *
     * @var string
     */
    protected $id_name;

    /**
     * La primary key
     *
     * @var string array
     */
    protected $primary_key;

    /**
     * Liste des colonnes avec le booléen correspondant
     *
     * @var array
     */
    protected $are_nullables;

    /**
     * Liste des colonnes ayant une valeur par défaut, avec la valeur par défaut associée
     *
     * @var array
     */
    protected $column_defaults;

    /**
     * Constructeur
     * Les attributs $table_name et $table_type doivent être déclarés dans la méthode init() des classes dérivées.
     *
     * @param ServiceLocatorInterface $sm            
     * @param ObjectDataInterface $objectData            
     */
    public function createService(ServiceLocatorInterface $db_manager)
    {
        if ($db_manager instanceof \SbmCommun\Model\Db\Service\DbManager) {
            $this->db_manager = $db_manager;
        } else {
            $type = gettype($db_manager);
            $message = 'Le service manager fourni n\'est pas un \\SbmCommun\\Model\Db\\Service\\DbManager. %s fourni.';
            throw new Exception(sprintf(_($message), $type));
        }
        $this->init();
        $this->primary_key = $db_manager->getPrimaryKey($this->table_name, $this->table_type);
        $this->are_nullables = $db_manager->getAreNullableColumns($this->table_name, $this->table_type);
        $this->column_defaults = $db_manager->getColumnDefaults($this->table_name, $this->table_type);
        $this->table_gateway = $db_manager->get($this->table_gateway_alias);
        $this->obj_select = clone $this->table_gateway->getSql()->select(); // utile pour join() et pour paginator()
        $this->join();
        // à placer après join()
        $this->obj_data = clone $this->table_gateway->getResultSetPrototype()->getObjectPrototype();
        $this->obj_data->setArrayMask($this->getColumnsNames());
        try {
            $this->obj_data->setAreNullable($this->db_manager->getAreNullableColumns($this->table_name, $this->table_type));
        } catch (\SbmCommun\Model\Db\Exception $e) {
            die('<!DOCTYPE Html><head><meta charset="utf-8"><title>SBM School Bus Manager</title></head><body>Il faut installer les tables dans la base de données.</body></html>');
        }
        if (is_callable(array(
            $this->table_gateway->getResultSetPrototype(),
            'getHydrator'
        ))) {
            $this->hydrator = $this->table_gateway->getResultSetPrototype()->getHydrator();
            $this->setStrategies();
        }
        return $this;
    }

    protected abstract function init();

    /**
     * Met en place les Zend\Stdlib\Hydrator\Strategy\StrategyInterface pour l'hydrator.
     * Ces stratégies sont définies dans Sbm\Model\Strategy
     * A surcharger dans les classes dérivées si nécessaire
     */
    protected function setStrategies()
    {}

    /**
     * Complète la requête pour le select() et le fetchPaginator().
     * A surcharger dans les classes dérivées si nécessaire
     */
    protected function join()
    {}

    /**
     * Renvoie l'objet d'échange de données
     *
     * @return SbmCommun\Model\Db\ObjectData\ObjectDataInterface
     */
    public function getObjData()
    {
        return clone $this->obj_data;
    }

    /**
     * Renvoie l'objet $table_gateway
     *
     * @return TableGateway
     */
    public function getTableGateway()
    {
        return $this->table_gateway;
    }

    /**
     * Renvoie le nom court de la table (sans indicateur de type et sans préfixe)
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    /**
     * Renvoie le type de la table (table | system | vue)
     *
     * @return string
     */
    public function getTableType()
    {
        return $this->table_type;
    }

    /**
     * Renvoie les nom des colonnes (champs de la table)
     *
     * @return array
     */
    public function getColumnsNames()
    {
        return $this->db_manager->getMetadata()->getColumnNames($this->db_manager->getCanonicName($this->table_name, $this->table_type));
    }

    /**
     * Renvoie un objet Zend\Db\Sql\Select pour un paginator
     *
     * @param \Zend\Db\Sql\Where $where_obj            
     * @param array|string|null $order            
     * @return Zend\Db\Sql\Select
     */
    private function select($where_obj = null, $order = null)
    {
        if (! is_null($where_obj)) {
            if ($where_obj instanceof Where) {
                $this->obj_select->where($where_obj);
            } else {
                throw new Exception(_('Zend\Db\Sql\Where instance expected.'));
            }
        }
        if (! is_null($order)) {
            $this->obj_select->order($order);
        }
        return $this->obj_select;
    }

    /**
     * Retourne un Zend\Paginator\Paginator basé sur la requête.
     * Les résultats sont hydratés et sont conformes au ResultSetPrototype du TableGateway
     *
     * @param Where|\Closure|string|array|Predicate\PredicateInterface $where_obj            
     * @param array|string|null $order            
     * @return Zend\Paginator\Paginator
     */
    public function paginator($where_obj = null, $order = null)
    {
        return new Paginator(new DbSelect($this->select($where_obj, $order), $this->db_manager->getDbadapter(), $this->table_gateway->getResultSetPrototype()));
    }

    /**
     * Retourne le contenu de la table.
     * Les résultats sont hydratés et sont conformes au ResultSetPrototype du TableGateway
     *
     * @param \Zend\Db\Sql\Where|null $where            
     * @param array|string|null $order            
     * @param string $combination
     *            One of the OP_* constants from Predicate\PredicateSet
     *            
     * @throws Exception
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    public function fetchAll($where = null, $order = null, $combination = PredicateSet::OP_AND)
    {
        if (! $this->table_gateway->isInitialized()) {
            $this->initialize();
        }
        
        $select = $this->table_gateway->getSql()->select();
        
        if ($where !== null) {
            $select->where($where, $combination);
        }
        if ($order !== null) {
            $select->order($order);
        }
        try {
            return $this->table_gateway->selectWith($select);
        } catch (\Exception $e) {
            $msg = __METHOD__ . ' - ' . $this->table_name . "\n";
            $msg .= $e->getMessage();
            if (is_string($where)) {
                $msg .= "\n WHERE = (" . $where . ')';
            } else {
                $msg .= "\n" . $select->getSqlString($this->db_manager->getDbAdapter()->platform);
            }
            if (getenv('APPLICATION_ENV') != 'development') {
                $msg = "Impossible d'exécuter la requête.";
            }
            throw new Exception($msg, $e->getCode(), $e->getPrevious());
            // die("<!DOCTYPE Html><html><head></head><body><pre>$msg</pre></body></html>");
        }
    }

    /**
     * Retourne l'enregistrement d'identifiant donné.
     * Le résultat est hydraté et est conforme au ResultSetPrototype du TableGateway
     *
     * @param int|string|array $id
     *            Si c'est un tableau, ce doit être un tableau associatif
     *            
     * @throws Exception
     * @return \SbmCommun\Model\Db\ObjectData\ObjectDataInterface null
     */
    public function getRecord($id)
    {
        if (is_array($id)) {
            $array_where = array();
            $condition_msg = array();
            foreach ($id as $key => $condition) {
                $array_where[$key . ' = ?'] = $condition;
                $condition_msg[] = "$key = $condition";
            }
            $condition_msg = implode(' et ', $condition_msg);
        } else {
            $array_where = array(
                $this->id_name . ' = ?' => $id
            );
            $condition_msg = $this->id_name . " = $id";
        }
        
        $rowset = $this->table_gateway->select($array_where);
        $row = $rowset->current();
        if (! $row) {
            throw new Exception(sprintf(_("Could not find row '%s' in table %s"), $condition_msg, $this->table_name));
        }
        return $row;
    }

    public function is_newRecord($id)
    {
        if ($id === false)
            return true;
        try {
            $this->getRecord($id);
            return false;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * Supprime l'enregistrement d'identifiant donné ou les enregistrements de where donnés
     *
     * @param int|string|array|Where|ObjectDataInterface $item
     *            identifiant ou where
     * @return int
     */
    public function deleteRecord($item)
    {
        if ($item instanceof ObjectDataInterface) {
            $array_where = $item->getId();
        } elseif (is_array($item) || $item instanceof Where) {
            $array_where = $item;
        } else {
            $array_where = array(
                $this->id_name . ' = ?' => $item
            );
        }
        
        return $this->table_gateway->delete($array_where);
    }

    /**
     * Enregistre l'objet $obj_data dans sa table en distinguant un nouvel enregistrement d'une mise à jour.
     * L'objet passé en paramètre doit définir la méthode getId().
     *
     * @param ObjectDataInterface $obj_data            
     * @throws Exception
     */
    public function saveRecord(ObjectDataInterface $obj_data)
    {
        if (! is_null($this->hydrator)) {
            $data = $this->hydrator->extract($obj_data);
        } else {
            $data = $obj_data->getArrayCopy();
        }
        if ($this->is_newRecord($obj_data->getId())) {
            $this->table_gateway->insert($this->prepareDataForInsert($data));
        } else {
            $id = $obj_data->getId();
            if ($this->getRecord($id)) {
                if (is_array($id)) {
                    $array_where = $id;
                    $condition_msg = array();
                    foreach ($id as $key => $condition) {
                        $condition_msg[] = "$key = $condition";
                    }
                    $condition_msg = implode(' et ', $condition_msg);
                } else {
                    $array_where = array(
                        $this->id_name . ' = ?' => $id
                    );
                    $condition_msg = $this->id_name . " = $id";
                }
                
                $this->table_gateway->update($this->prepareDataForUpdate($data), $array_where);
            } else {
                throw new Exception(sprintf(_("This is not a new data and the id '%s' can not be found in the table %s."), $condition_msg, $this->table_name));
            }
        }
    }

    /**
     * Mise à jour d'un enregistrement dans une table.
     * Si l'enregistrement est absent on lance une exception
     * ATTENTION !!!
     * Cette méthode ne convient pas lorsqu'on change la pk (ou une partie de la pk lorsqu'elle est basée sur plusieurs colonnes)
     *
     * @param ObjectData $obj_data            
     * @throws Exception
     */
    public function updateRecord(ObjectDataInterface $obj_data)
    {
        if (! is_null($this->hydrator)) {
            $data = $this->hydrator->extract($obj_data);
        } else {
            $data = $obj_data->getArrayCopy();
        }
        $id = $obj_data->getId();
        if ($this->getRecord($id)) {
            if (is_array($id)) {
                $array_where = $id;
                $condition_msg = array();
                foreach ($id as $key => $condition) {
                    $condition_msg[] = "$key = $condition";
                }
                $condition_msg = implode(' et ', $condition_msg);
            } else {
                $array_where = array(
                    $this->id_name . ' = ?' => $id
                );
                $condition_msg = $this->id_name . " = $id";
            }
            
            $this->table_gateway->update($this->prepareDataForUpdate($data), $array_where);
        } else {
            throw new Exception(sprintf(_("This is not a new data and the id '%s' can not be found in the table %s."), $condition_msg, $this->table_name));
        }
    }

    /**
     *
     * @todo écrire la préparation des données de type auto_increment, numeric ou datetime
     *      
     * @param array $data            
     *
     * @return array
     */
    protected function prepareDataForInsert($data)
    {
        foreach ($data as $key => &$value) {
            if ($value === '') {
                if ($this->db_manager->isAutoIncrement($key, $this->table_name, $this->table_type)) {
                    $value = null;
                } elseif ($this->db_manager->isDateTimeColumn($key, $this->table_name, $this->table_type) || $this->db_manager->isNumericColumn($key, $this->table_name, $this->table_type)) {
                    if ($this->are_nullables[$key]) {
                        $value = null;
                    } elseif (array_key_exists($key, $this->column_defaults)) {
                        $value = $this->column_defaults[$key];
                    }
                }
            }
        }
        return $data;
    }

    /**
     *
     * @todo écrire la préparation des données de type auto_increment, numeric ou datetime
     *      
     * @param array $data            
     *
     * @return array
     */
    protected function prepareDataForUpdate($data)
    {
        foreach ($data as $key => &$value) {
            if ($value === '') {
                if ($this->db_manager->isDateTimeColumn($key, $this->table_name, $this->table_type) || $this->db_manager->isNumericColumn($key, $this->table_name, $this->table_type)) {
                    if ($this->are_nullables[$key]) {
                        $value = null;
                    } elseif (array_key_exists($key, $this->column_defaults)) {
                        $value = $this->column_defaults[$key];
                    }
                }
            }
        }
        return $data;
    }
    

    /**
     * Remie à zéro de la sélection des fiches de la table.
     * Plus aucune fiche ne sera sélectionnée.
     * Encore faut-il que la table ait un champ `selection` !!!
     *
     * @return int
     */
    public function clearSelection()
    {
        return $this->getTableGateway()->update(array(
            'selection' => 0
        ));
    }
}