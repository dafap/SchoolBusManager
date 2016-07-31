<?php
/**
 * Service donnant un Tablegateway pour les tables de Sbm
 * (à dériver pour chaque table en définissant la méthode init())
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource AbstractSbmTableGateway.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 juill. 2016
 * @version 2016-2.1.10
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\TableGateway\TableGateway;
use SbmCommun\Model\Db\ResultSet\HydratingResultSet;

abstract class AbstractSbmTableGateway implements FactoryInterface
{
    protected $table_name;
    protected $type;
    protected $data_object_alias;
    /**
     * Cette propriété sert dans la construction de $resultSetPrototype (1er paramètre du constructeur de HydratingResultSet)
     * Lorsque cette propriété est nulle, l'hydrator par défaut est utilisé : Zend\Stdlib\Hydrator\ArraySerializable.
     * Les classes dérivées de AbstractSbmTableGateway peuvent surcharger l'hydrator dans la méthode init()
     * 
     * @var HydratorInterface
     */
    protected $hydrator = null;
    public function createService(ServiceLocatorInterface $db_manager)
    {
        if (!($db_manager instanceof \SbmCommun\Model\Db\Service\DbManager)) {
            $type = gettype($db_manager);
            $message = 'Le service manager fourni n\'est pas un \\SbmCommun\\Model\\Db\\Service\\DbManager. %s fourni.';
            throw new Exception($message, $type);
        }
        $this->init();        
        $table_name = $db_manager->getCanonicName($this->table_name, $this->type);
        $dbAdapter = $db_manager->getDbAdapter();
        $data_object = $db_manager->get($this->data_object_alias);
        $resultSetPrototype = new HydratingResultSet($this->hydrator, $data_object);
        return new TableGateway($table_name, $dbAdapter, null, $resultSetPrototype);
    }
    
    protected abstract function init();
}