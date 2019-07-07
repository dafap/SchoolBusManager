<?php
/**
 * Requêtes sur la table `history` permettant d'initialiser des listes déroulantes (Select)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource HistoryForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 juil. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Select;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class HistoryForSelect implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     *
     * @var string
     */
    private $table_name;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->sql = new Sql($this->db_manager->getDbAdapter());
        $this->table_name = $this->db_manager->getCanonicName('history', 'system');
        return $this;
    }

    /**
     * Renvoie un tableau des exercices présents dans la table `history`
     *
     * @return array
     */
    public function paiementExercices()
    {
        $where = new Where();
        $where->equalTo('table_name', $this->db_manager->getCanonicName('paiements'));
        // ->notEqualTo('action', 'insert');
        $select = $this->sql->select($this->table_name)
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([
            'exercice' => 'id_txt'
        ])
            ->where($where)
            ->order('id_txt');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['exercice']] = $row['exercice'];
        }
        return $array;
    }
}