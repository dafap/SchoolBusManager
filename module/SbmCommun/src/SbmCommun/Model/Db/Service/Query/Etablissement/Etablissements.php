<?php
/**
 * Requêtes pour extraire des etablissements
 *
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Etablissement
 * @filesource Etablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 avr. 2016
 * @version 2016-2
 */
namespace SbmCommun\Model\Db\Service\Query\Etablissement;
 
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapSession\Model\Session;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class Etablissements implements FactoryInterface
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
     * @var int
     */
    protected $millesime;
    
    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    protected $sql;

    /**
     * Renvoie la chaine de requête (après l'appel de la requête)
     *
     * @param \Zend\Db\Sql\Select $select
     *
     * @return \Zend\Db\Adapter\mixed
     */
    public function getSqlString($select)
    {
        return $select->getSqlString($this->dbAdapter->getPlatform());
    }
    
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->millesime = Session::get('millesime');
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        return $this;
    }
    
    /**
     * Requête préparée renvoyant la position géographique des établissements,
     * @param Where $where
     * @param string $order
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getLocalisation(Where $where, $order = null)
    {
        $select = $this->selectLocalisation($where, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();;
    }
    
    private function selectLocalisation(Where $where, $order = null)
    {
        $select = clone $this->sql->select();
        $select->from(array(
            'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
        ))
            ->columns(array(
            'nom',
            'x',
            'y'
        ))
            ->join(array(
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ), 'eta.communeId=com.communeId', array(
            'commune' => 'nom'
        ));
        if (! is_null($order)) {
            $select->order($order);
        }
        return $select->where($where);
    }
}
