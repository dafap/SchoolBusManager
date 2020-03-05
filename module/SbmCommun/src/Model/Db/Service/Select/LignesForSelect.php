<?php
/**
 * Service fournissant une liste des lignes sous la forme d'un tableau
 *   'ligneId' => 'code - Départ - Arrivée'
 *
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource LignesForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 fév. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Select;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Select;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmBase\Model\Session;

class LignesForSelect implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

    /**
     *
     * @var string
     */
    private $table_name;

    /**
     *
     * @var string
     */
    private $table_lien;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->table_name = $this->db_manager->getCanonicName('lignes', 'table');
        $this->table_lien = $this->db_manager->getCanonicName('etablissements-services',
            'table');
        $this->sql = new Sql($this->db_manager->getDbAdapter());
        return $this;
    }

    /**
     * Service fournissant une liste des lignes sous la forme d'un tableau 'ligneId' =>
     * 'ligneId (départ - arrivée)'
     *
     * @return array Renvoie un tableau structuré
     */
    public function tout()
    {
        $libelle = new Literal(
            'concat(ligneId, " (", extremite1, " - ", extremite2, ")")');
        $select = $this->sql->select($this->table_name);
        $select->columns([
            'ligneId',
            'libelle' => $libelle
        ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->order('ligneId');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['ligneId']] = $row['libelle'];
        }
        return $array;
    }

    /**
     * Renvoie la liste des lignes desservant un établissement le matin
     *
     * @param string $etablissementId
     *
     * @return array
     */
    public function to(string $etablissementId)
    {
        $libelle = new Literal(
            'concat(ligneId, " (", extremite1, " - ", extremite2, ")")');
        $select = $this->sql->select([
            's' => $this->table_name
        ]);
        $select->columns([
            'ligneId',
            'libelle' => $libelle
        ])
            ->join([
            'es' => $this->table_lien
        ], 's.millesime = es.millesime AND s.ligneId = es.ligneId', [])
            ->where(
            [
                'etablissementId' => $etablissementId,
                'millesime' => Session::get('millesime'),
                'moment' => 1
            ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->order('ligneId');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['ligneId']] = $row['libelle'];
        }
        return $array;
    }

    /**
     * Renvoie la liste des lignes desservant un établissement le midi ou le soir
     *
     * @param string $etablissementId
     *
     * @return array
     */
    public function from(string $etablissementId)
    {
        $libelle = new Literal(
            'concat(ligneId, " (", extremite1, " - ", extremite2, ")")');
        $select = $this->sql->select([
            's' => $this->table_name
        ]);
        $select->columns([
            'ligneId',
            'libelle' => $libelle
        ])
            ->join([
            'es' => $this->table_lien
        ], 's.millesime = es.millesime AND s.ligneId = es.ligneId', [])
            ->where(
            (new Where())->equalTo('etablissementId', $etablissementId)
                ->greaterThan('moment', 1)
                ->equalTo('millesime', Session::get('millesime')))
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->order('ligneId');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['ligneId']] = $row['libelle'];
        }
        return $array;
    }
}