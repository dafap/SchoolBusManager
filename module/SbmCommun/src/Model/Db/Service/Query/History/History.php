<?php
/**
 * Requêtes sur la table système `history`
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/History
 * @filesource History.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Model\Db\Service\Query\History;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class History implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

    /**
     *
     * @var string
     */
    private $history_name;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

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
        $this->history_name = $this->db_manager->getCanonicName('history', 'sys');
        return $this;
    }

    /**
     * Changements du dernier jour
     * On vérifie si la table affectation a été modifié pour le millesime indiqué
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getLastDayChanges($table_name, $millesime)
    {
        $select = $this->sql->select($this->history_name);
        $table_affectations = $this->db_manager->getCanonicName($table_name);
        $hier = date('Y-m-d H:i', strtotime('-1 day'));
        $where = new Where();
        $where->equalTo('table_name', $table_affectations)
            ->greaterThanOrEqualTo('dt', $hier)
            ->like('id_txt', "$millesime%");
        $select->columns([
            'id_txt',
            'log'
        ])
            ->where($where)
            ->order([
            'dt'
        ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
}