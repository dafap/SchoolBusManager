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
 * @date 8 juin 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Query\Etablissement;
 
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapSession\Model\Session;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class Etablissements implements FactoryInterface
{
    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbLibService
     */
    protected $db;
    
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
        $this->millesime = Session::get('millesime');
        $this->db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->dbAdapter = $this->db->getDbAdapter();
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
            'eta' => $this->db->getCanonicName('etablissements', 'table')
        ))
            ->columns(array(
            'nom',
            'x',
            'y'
        ))
            ->join(array(
            'com' => $this->db->getCanonicName('communes', 'table')
        ), 'eta.communeId=com.communeId', array(
            'commune' => 'nom'
        ));
        if (! is_null($order)) {
            $select->order($order);
        }
        return $select->where($where);
    }
}
