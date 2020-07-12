<?php
/**
 * Service fournissant une liste des services sous la forme d'un tableau
 *   'serviceId' => 'nom'
 * où serviceId est une chaine de la forme ligneId|sens|moment|ordre
 *
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource ServicesForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 juil. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Select;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmCommun\Model\Traits\ServiceTrait;
use SbmCommun\Model\Traits\ExpressionSqlTrait;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use SbmBase\Model\Session;
use Zend\Db\Sql\Predicate\Predicate;

class ServicesForSelect implements FactoryInterface
{
    use ServiceTrait, ExpressionSqlTrait, SelectTrait;

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
    private $sql;

    /**
     *
     * @var string
     */
    private $table_name;

    /**
     * Liste des colonnes utilisées par les méthodes de cette classe
     *
     * @var \Zend\Db\Sql\Literal[]
     */
    private $columns;

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
        $this->millesime = Session::get('millesime');
        $this->table_name = $this->db_manager->getCanonicName('services', 'vue');
        $this->table_lien = $this->db_manager->getCanonicName('etablissements-services',
            'table');
        $this->sql = new Sql($this->db_manager->getDbAdapter());
        $this->columns = $this->getServiceKeys(); // à faire en premier
        return $this;
    }

    /**
     * Renvoie un tableau structuré Service fournissant une liste des services sous la
     * forme d'un tableau 'serviceId' => 'serviceId - nom (operateur - transporteur)'
     *
     * @return array
     */
    public function tout()
    {
        $select = $this->sql->select($this->table_name);
        $this->columns['libelle'] = new Literal($this->getSqlDesignationService());
        $select->columns($this->columns)
            ->where([
            'millesime' => $this->millesime
        ])
            ->order($this->getServiceKeys());
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$this->encodeServiceId($row)] = $row['libelle'];
        }
        return $array;
    }

    /**
     * Liste des services desservant un établissement (éventuellement à un moment donné)
     *
     * @param string $etablissementId
     * @param int $moment
     *
     * @return array
     */
    public function desservent(string $etablissementId, int $moment = 0)
    {
        $conditions = [
            'millesime' => $this->millesime,
            'etablissementId' => $etablissementId
        ];
        if ($moment) {
            $conditions['moment'] = $moment;
        }
        $this->columns['libelle'] = new Literal(
            $this->getSqlChoixService('s.ligneId', 's.sens', 's.moment', 's.ordre',
                's.semaine'));
        $select = $this->sql->select([
            's' => $this->table_name
        ])
            ->columns($this->columns)
            ->join([
            'es' => $this->table_lien
        ], $this->jointureService('s', 'es'), [])
            ->where($conditions)
            ->order($this->getServiceKeys());
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$this->encodeServiceId($row)] = $row['libelle'];
        }
        return $array;
    }

    /**
     * Renvoie la liste des services permettant un déplacement depuis le service donné en
     * paramètres
     *
     * @param string $ligneId
     * @param int $sens
     * @param int $moment
     * @param int $ordre
     *
     * @return array
     */
    public function deplacement(string $ligneId, int $sens, int $moment, int $ordre)
    {
        $where = new Where();
        $where->equalTo('aff.millesime', $this->millesime)
            ->equalTo('aff.ligne1Id', $ligneId)
            ->equalTo('aff.sensligne1', $sens)
            ->equalTo('aff.moment', $moment)
            ->equalTo('aff.ordreligne1', $ordre)
            ->literal('l.actif = 1')
            ->literal('s.actif = 1')
            ->nest()
            ->notEqualTo('aff.ligne1Id', 'cir1.ligneId', Predicate::TYPE_IDENTIFIER,
            Predicate::TYPE_IDENTIFIER)->or->notEqualTo('aff.sensligne1', 'cir1.sens',
            Predicate::TYPE_IDENTIFIER, Predicate::TYPE_IDENTIFIER)->or->notEqualTo(
            'aff.ordreligne1', 'cir1.ordre', Predicate::TYPE_IDENTIFIER,
            Predicate::TYPE_IDENTIFIER)
            ->unnest()
            ->nest()
            ->equalTo('aff.station1Id', 'cir1.stationId', Predicate::TYPE_IDENTIFIER,
            Predicate::TYPE_IDENTIFIER)->or->equalTo('jum1.station1Id', 'cir1.stationId',
            Predicate::TYPE_IDENTIFIER, Predicate::TYPE_IDENTIFIER)->or->equalTo(
            'jum1.station2Id', 'cir1.stationId', Predicate::TYPE_IDENTIFIER,
            Predicate::TYPE_IDENTIFIER)->or->equalTo('jum2.station1Id', 'cir1.stationId',
            Predicate::TYPE_IDENTIFIER, Predicate::TYPE_IDENTIFIER)->or->equalTo(
            'jum2.station2Id', 'cir1.stationId', Predicate::TYPE_IDENTIFIER,
            Predicate::TYPE_IDENTIFIER)
            ->unnest()
            ->nest()
            ->equalTo('aff.station2Id', 'cir2.stationId', Predicate::TYPE_IDENTIFIER,
            Predicate::TYPE_IDENTIFIER)->or->equalTo('jum3.station1Id', 'cir2.stationId',
            Predicate::TYPE_IDENTIFIER, Predicate::TYPE_IDENTIFIER)->or->equalTo(
            'jum3.station2Id', 'cir2.stationId', Predicate::TYPE_IDENTIFIER,
            Predicate::TYPE_IDENTIFIER)->or->equalTo('jum4.station1Id', 'cir2.stationId',
            Predicate::TYPE_IDENTIFIER, Predicate::TYPE_IDENTIFIER)->or->equalTo(
            'jum4.station2Id', 'cir2.stationId', Predicate::TYPE_IDENTIFIER,
            Predicate::TYPE_IDENTIFIER)
            ->unnest()
            ->lessThan('cir1.horaireD', 'cir2.horaireA', Predicate::TYPE_IDENTIFIER,
            Predicate::TYPE_IDENTIFIER);
        $select = $this->sql->select(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([])
            ->join([
            'cir1' => $this->db_manager->getCanonicName('circuits', 'table')
        ], 'aff.millesime=cir1.millesime AND aff.moment=cir1.moment', [])
            ->join([
            'cir2' => $this->db_manager->getCanonicName('circuits', 'table')
        ],
            'cir1.millesime=cir2.millesime AND cir1.ligneId=cir2.ligneId AND cir1.sens=cir2.sens AND cir1.moment=cir2.moment AND cir1.ordre=cir2.ordre',
            [])
            ->join([
            's' => $this->db_manager->getCanonicName('services', 'table')
        ],
            'cir1.millesime=s.millesime AND cir1.ligneId=s.ligneId AND cir1.sens=s.sens AND cir1.moment=s.moment AND cir1.ordre=s.ordre',
            [
                'ligneId',
                'sens',
                'moment',
                'ordre',
                'libelle' => new Literal(
                    $this->getSqlChoixService('s.ligneId', 's.sens', 's.moment', 's.ordre',
                        's.semaine'))
            ])
            ->join([
            'l' => $this->db_manager->getCanonicName('lignes', 'table')
        ], 'l.millesime=s.millesime AND l.ligneId=s.ligneId', [])
            ->join(
            [
                'jum1' => $this->db_manager->getCanonicName('stations-stations', 'table')
            ], 'aff.station1Id = jum1.station1Id', [], Select::JOIN_LEFT)
            ->join(
            [
                'jum2' => $this->db_manager->getCanonicName('stations-stations', 'table')
            ], 'aff.station1Id = jum2.station2Id', [], Select::JOIN_LEFT)
            ->join(
            [
                'jum3' => $this->db_manager->getCanonicName('stations-stations', 'table')
            ], 'aff.station2Id = jum3.station1Id', [], Select::JOIN_LEFT)
            ->join(
            [
                'jum4' => $this->db_manager->getCanonicName('stations-stations', 'table')
            ], 'aff.station2Id = jum4.station2Id', [], Select::JOIN_LEFT)
            ->where($where)
            ->order([
            's.ligneId',
            's.sens',
            's.moment',
            's.ordre'
        ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$this->encodeServiceId($row)] = $row['libelle'];
        }
        return $array;
    }
}