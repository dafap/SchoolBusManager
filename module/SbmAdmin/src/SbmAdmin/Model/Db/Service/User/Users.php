<?php
/**
 * Requêtes sur la table users pour ce module
 *
 * Compatibilité ZF3 
 * 
 * @project sbm
 * @package SbmAdmin/Model/Db/Service/User
 * @filesource Users.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 avr. 2016
 * @version 2016-2
 */
namespace SbmAdmin\Model\Db\Service\User;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Adapter\Adapter;
use SbmCommun\Model\Db\Service\DbManager;

class Users implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    protected $db_manager;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    protected $sql;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        return $this;
    }

    public function deleteParentsNonConfirmes()
    {
        $where = new Where();
        $where->equalTo('confirme', 0)->equalTo('categorieId', 1);
        $query = $this->sql->delete($this->db_manager->getCanonicName('users', 'table'))
            ->where($where);
        $sqlString = $this->sql->buildSqlString($query);
        return $this->dbAdapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }
}