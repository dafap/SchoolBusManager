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
 * @date 29 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Select;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TarifsForSelect implements FactoryInterface
{

    private $millesime;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->table_name = $this->db_manager->getCanonicName('tarifs', 'table');
        $this->sql = new Sql($this->db_manager->getDbAdapter());
        $libelle = new Literal('concat(nom, " ", grille, " (", montant, ")")');
        $this->columns = [
            'tarifId',
            'libelle' => $libelle
        ];
        $this->order = [
            'grille',
            'reduction',
            'seuil'
        ];
        $this->setMillesime();
        return $this;
    }

    public function setMillesime($millesime = null)
    {
        if (is_null($millesime)) {
            $this->millesime = Session::get('millesime');
        } else {
            $this->millesime = $millesime;
        }
        return $this;
    }

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

    public function toutes()
    {
        $select = $this->sql->select($this->table_name);
        $select->columns($this->columns)
            ->where([
            'millesime' => $this->millesime
        ])
            ->order($this->order);
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
        $where->equalTo('grille', $n)->equalTo('millesime', $this->millesime);
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