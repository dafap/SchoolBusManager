<?php
/**
 * Service fournissant une liste des communes visibles sous la forme d'un tableau
 *   'communeId' => 'nom (département)'
 * Le département est codé sur 2 chiffres.
 * La liste est ordonnées en plaçant les communes du département 12 en premier (ordre alphabétique)
 * puis la liste des autres communes dans l'ordre alphabétique.
 *
 *   Attention !
 * L'utilisation de ces listes par ajax nécessite, avant d'encoder le tableau au format JSON, d'inverser
 * les clés et les valeurs afin de ne pas perdre l'ordre du tableau. En effet, les clés étant numériques,
 * la plupart des navigateurs (sauf Firefox) ordonnent le tableau dans l'ordre croissant des clés. En
 * passant par l'inversion, on donne des clés alphabétiques qui ne sont pas réorganisées par le navigateur.
 * On inverse les clés et les valeurs par la fonction array_flip().
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource CommunesForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 jan. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Select;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CommunesForSelect implements FactoryInterface
{

    private $db_manager;

    private $table_name;

    private $sql;

    private $myDep;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->table_name = $this->db_manager->getCanonicName('communes', 'table');
        $this->sql = new Sql($this->db_manager->getDbAdapter());
        $this->myDep = new Expression('CASE departement WHEN 73 THEN 0 ELSE 1 END');
        return $this;
    }

    /**
     * Retourne une liste ordonnée pour un début de nom donné
     *
     * @param string $like
     * @return multitype:string
     */
    public function nomLike($like)
    {
        $where = new Where();
        $where->like('nom', $like . '%');
        $select = $this->sql->select($this->table_name);
        $select->where($where);
        $select->columns(
            [
                'communeId',
                'alias',
                'departement',
                'myDep' => $this->myDep
            ]);
        $select->order([
            'myDep',
            'nom',
            'departement'
        ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['communeId']] = $row['alias'] . ' (' . $row['departement'] . ')';
        }
        return $array;
    }

    /**
     * Retourne une liste ordonnée pour un code postal donné
     *
     * @param string $cp
     * @return multitype:string
     */
    public function codePostal($cp)
    {
        if (! is_string($cp) && is_int($cp)) {
            // ça c'est pour les départements de AIN (01) à ARIEGE (09) qui pourraient
            // perdre le
            // zéro non sigificatif
            $cp = sprintf('%05d', $cp);
        }
        $where = new Where();
        $where->equalTo('codePostal', $cp);
        $select = $this->sql->select($this->table_name);
        $select->where($where);
        $select->columns(
            [
                'communeId',
                'alias',
                'departement',
                'myDep' => $this->myDep
            ]);
        $select->order([
            'myDep',
            'nom',
            'departement'
        ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['communeId']] = $row['alias'] . ' (' . $row['departement'] . ')';
        }
        return $array;
    }

    /**
     * Retourne la liste des communes visibles
     *
     * @return multitype:string
     */
    public function visibles()
    {
        $where = new Where();
        $where->literal('visible = 1');
        $select = $this->sql->select($this->table_name);
        $select->where($where);
        $select->columns(
            [
                'communeId',
                'alias',
                'departement',
                'myDep' => $this->myDep
            ]);
        $select->order([
            'myDep',
            'nom',
            'departement'
        ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['communeId']] = $row['alias'] . ' (' . $row['departement'] . ')';
        }
        return $array;
    }

    /**
     * Retourne la liste des communes desservies
     *
     * @return multitype:string
     */
    public function desservies()
    {
        $where = new Where();
        $where->literal('desservie = 1');
        $select = $this->sql->select($this->table_name);
        $select->where($where);
        $select->columns(
            [
                'communeId',
                'alias',
                'departement',
                'myDep' => $this->myDep
            ]);
        $select->order([
            'myDep',
            'nom',
            'departement'
        ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['communeId']] = $row['alias'] . ' (' . $row['departement'] . ')';
        }
        return $array;
    }

    /**
     * Retourne la liste des communes membres
     *
     * @return multitype:string
     */
    public function membres()
    {
        $where = new Where();
        $where->literal('membre = 1');
        $select = $this->sql->select($this->table_name);
        $select->where($where);
        $select->columns(
            [
                'communeId',
                'alias',
                'departement',
                'myDep' => $this->myDep
            ]);
        $select->order([
            'myDep',
            'nom',
            'departement'
        ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['communeId']] = $row['alias'] . ' (' . $row['departement'] . ')';
        }
        return $array;
    }
}