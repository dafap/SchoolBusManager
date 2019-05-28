<?php
/**
 * Service fournissant une liste des lots de marché sous la forme
 *   'lotId' => 'marche-lot (transporteur)'
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Select
 * @filesource LotsForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Select;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LotsForSelect implements FactoryInterface
{

    private $db_manager;

    private $table_name;

    private $vue_name;

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
        $this->table_name = $this->db_manager->getCanonicName('lots', 'table');
        $this->table_name = $this->db_manager->getCanonicName('lots', 'vue');
        return $this;
    }

    public function lotId()
    {
        $select = $this->sql->select($this->vue_name);
        $select->columns([
            'lotId',
            'marche',
            'lot',
            'titulaire'
        ]);
        $select->order([
            'actif DESC',
            'marche',
            'lot'
        ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['lotId']] = $row['marche'] . ' - ' . $row['lot'] . ' (' .
                $row['titulaire'] . ')';
        }
        return $array;
    }

    public function marche()
    {
        $select = $this->sql->select($this->table_name);
        $select->quantifier($select::QUANTIFIER_DISTINCT)
            ->columns([
            'marche'
        ])
            ->order([
            'actif DESC',
            'marche'
            ]);
            $statement = $this->sql->prepareStatementForSqlObject($select);
            $rowset = $statement->execute();
            $array = [];
            foreach ($rowset as $row) {
                $array[$row['marche']] = $row['marche'];
            }
            return $array;
    }

    public function lot()
    {
        $select = $this->sql->select($this->table_name);
        $select->quantifier($select::QUANTIFIER_DISTINCT)
        ->columns([
            'lot'
        ])
        ->order([
            'actif DESC',
            'lot'
        ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['lot']] = $row['lot'];
        }
        return $array;
    }

    public function dateFin()
    {
        $select = $this->sql->select($this->table_name);
        $select->quantifier($select::QUANTIFIER_DISTINCT)
        ->columns([
            'dateFin'
        ])
        ->order([
            'dateFin DESC'
        ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['dateFin']] = $row['dateFin'];
        }
        return $array;
    }
}