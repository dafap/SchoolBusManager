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
 * @date 17 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\Db\Sql\Where;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use SbmCommun\Model\Db\Service\Table\Exception;

abstract class AbstractSbmTable implements FactoryInterface
{

    /**
     * Descripteur de la base de données
     *
     * @var DbLib
     */
    protected $db;

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
     * @var TableGateway
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
     * Constructeur
     * Les attributs $table_name et $table_type doivent être déclarés dans la méthode init() des classes dérivées.
     *
     * @param ServiceLocatorInterface $sm            
     * @param ObjectDataInterface $objectData            
     */
    public function createService(ServiceLocatorInterface $sm)
    {
        $this->init();
        $this->db = $sm->get('Sbm\Db\DbLib');
        $this->primary_key = $this->db->getPrimaryKey($this->table_name, $this->table_type);
        
        $this->table_gateway = $sm->get($this->table_gateway_alias);
        $this->obj_select = clone $this->table_gateway->getSql()->select(); // utile pour join() et pour paginator()
        $this->join();
        // à placer après join()
        $this->obj_data = clone $this->table_gateway->getResultSetPrototype()->getObjectPrototype();
        $this->obj_data->setArrayMask($this->getColumnsNames());
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
     * @return \Bdts\Model\ObjectData\ObjectDataInterface
     */
    public function getObjData()
    {
        return $this->obj_data;
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
        return $this->db->getMetadata()->getColumnNames($this->db->getCanonicName($this->table_name, $this->table_type));
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
     * @param \Zend\Db\Sql\Where $where_obj            
     * @param array|string|null $order            
     * @return Zend\Paginator\Paginator
     */
    public function paginator($where_obj = null, $order = null)
    {
        return new Paginator(new DbSelect($this->select($where_obj, $order), $this->db->getDbadapter(), $this->table_gateway->getResultSetPrototype()));
    }

    /**
     * Retourne le contenu de la table.
     * Les résultats sont hydratés et sont conformes au ResultSetPrototype du TableGateway
     *
     * @param \Zend\Db\Sql\Where|null $where            
     * @param array|string|null $order            
     *
     * @return ResultSet
     */
    public function fetchAll($where = null, $order = null)
    {
        if (! $this->table_gateway->isInitialized()) {
            $this->initialize();
        }
        
        $select = $this->table_gateway->getSql()->select();
        
        if ($where !== null) {
            $select->where($where);
        }
        if ($order !== null) {
            $select->order($order);
        }
        return $this->table_gateway->selectWith($select);
    }

    /**
     * Retourne l'enregistrement d'identifiant donné.
     * Le résultat est hydraté et sont conforme au ResultSetPrototype du TableGateway
     *
     * @param int|string $id            
     * @throws Exception
     * @return \Bdts\Model\ObjectData\ObjectDataInterface null
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
        try {
            $this->getRecord($id);
            return false;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * Supprime l'enregistrement d'identifiant donné
     *
     * @param int|string $id            
     * @return int
     */
    public function deleteRecord($id)
    {
        if (is_array($id)) {
            $array_where = $id;
        } else {
            $array_where = array(
                $this->id_name . ' = ?' => $id
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
            $this->table_gateway->insert($data);
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
                
                $this->table_gateway->update($data, $array_where);
            } else {
                throw new Exception(sprintf(_("This is not a new data and the id '%s' can not be found in the table %s."), $condition_msg, $this->table_name));
            }
        }
    }
}