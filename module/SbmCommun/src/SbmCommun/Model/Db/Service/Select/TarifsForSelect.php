<?php
/**
 * Service fournissant une liste de tarifs sous la forme d'un tableau
 *   'tarifId' => 'montant' + 'nom' 
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource TarifsForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 août 2018
 * @version 2018-2.4.4
 */
namespace SbmCommun\Model\Db\Service\Select;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Literal;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class TarifsForSelect implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->table_name = $this->db_manager->getCanonicName('tarifs', 'table');
        $this->sql = new Sql($this->db_manager->getDbAdapter());
        $libelle = new Literal('concat(nom, " (", montant, ")")');
        $this->columns = [
            'tarifId',
            'libelle' => $libelle
        ];
        $this->order = [
            'grille',
            'rythme',
            'mode'
        ];
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

    public function toutes()
    {
        $where = new Where();
        $select = $this->sql->select($this->table_name);
        $select->columns($this->columns)->order($this->order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['tarifId']] = $row['libelle'];
        }
        return $array;
    }

    public function grille($n)
    {
        $where = new Where();
        $where->equalTo('grille', $n);
        $select = $this->sql->select($this->table_name);
        $select->where($where)
            ->columns($this->columns)
            ->order($this->order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['tarifId']] = $row['libelle'];
        }
        return $array;
    }
}