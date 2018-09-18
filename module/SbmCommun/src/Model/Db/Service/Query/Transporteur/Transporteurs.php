<?php
/**
 * Requêtes portant sur les transporteurs
 * (classe déclarée dans module.config.php sous l'alias 'Sbm\Db\Query\Transporteurs')
 *
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Transporteur
 * @filesource Transporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Model\Db\Service\Query\Transporteur;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Transporteurs implements FactoryInterface
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

    /**
     * Renvoie la chaine de requête (après l'appel de la requête)
     *
     * @param \Zend\Db\Sql\Select $select
     *
     * @return string
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
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        return $this;
    }

    /**
     * Renvoie la liste des emails des utilisateurs associés à un transporteur
     *
     * @param int $transporteurId
     * @param string|array $order
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getUserEmails($transporteurId, $order = null)
    {
        $select = $this->selectUserEmails($transporteurId, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function selectUserEmails($transporteurId, $order)
    {
        $select = $this->sql->select(
            [
                'ut' => $this->db_manager->getCanonicName('users-transporteurs', 'table')
            ])
            ->join([
            'u' => $this->db_manager->getCanonicName('users', 'table')
        ], 'u.userId = ut.userId', [
            'email'
        ])
            ->columns([
            'nomprenom' => new Expression('CONCAT(u.prenom, " ", u.nom)')
        ]);
        if (! empty($order)) {
            $select->order($order);
        }
        $where = new Where();
        $where->equalTo('ut.transporteurId', $transporteurId);
        // die($this->getSqlString($select));
        return $select->where($where);
    }
}