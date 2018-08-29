<?php
/**
 * Méthodes calculant les montants payés et à payer par un responsable
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Responsable
 * @filesource CalculMontant.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 août 2018
 * @version 2018-2.4.4
 */
namespace SbmCommun\Model\Db\Service\Query\Responsable;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use SbmBase\Model\Session;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class CalculMontant implements FactoryInterface
{

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     *
     * @var int
     */
    private $millesime;

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
        $this->millesime = Session::get('millesime');
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        return $this;
    }

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

    public function selectDroitsInscription($responsableId = null)
    {
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)
            ->equalTo('inscrit', 1)
            ->equalTo('gratuit', 0)
            ->equalTo('fa', 0); // inscrit, ni gratuit, ni organisme, ni fa
        
        $select = new Select();
        $select->from(
            [
                'tar' => $this->db_manager->getCanonicName('tarifs', 'table')
            ])
            ->join(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ], 'tar.tarifId = sco.tarifId', [])
            ->join(
            [
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ], 'sco.eleveId = ele.eleveid', [])
            ->join(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'ele.responsable1Id = res.responsableId', ['responsableId'])
            ->columns(
            [
                
                'montant' => new Expression('SUM(`tar`.`montant`)')
            ])
            ->where($where)->group('responsableId');
        if ($responsableId) {
            $select->having((new Where())->equalTo('responsableId', $responsableId));
        }
        //die($this->getSqlString($select));
        return $select;
    }

    public function droitsInscription($responsableId)
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectDroitsInscription($responsableId));
        $result = $statement->execute();
        $total = 0;
        foreach ($result as $row) {
            $total += $row['montant'];
        }
        return $total;
    }

    public function resteAPayer($responsableId)
    {
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)
            ->equalTo('responsableId', $responsableId)
            ->equalTo('inscrit', 1)
            ->
        // inscrit
        equalTo('gratuit', 0)
            ->
        // ni gratuit, ni organisme
        equalTo('fa', 0)
            ->
        // pas fa
        equalTo('paiement', 0); // préinscrit
        
        $select = new Select();
        $select->from(
            [
                'tar' => $this->db_manager->getCanonicName('tarifs', 'table')
            ])
            ->join(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ], 'tar.tarifId = sco.tarifId', [])
            ->join(
            [
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ], 'sco.eleveId = ele.eleveid', [])
            ->join(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'ele.responsable1Id = res.responsableId', [])
            ->columns(
            [
                'montant' => new Expression('SUM(`tar`.`montant`)')
            ])
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $total = 0;
        foreach ($result as $row) {
            $total += $row['montant'];
        }
        return $total;
    }
}