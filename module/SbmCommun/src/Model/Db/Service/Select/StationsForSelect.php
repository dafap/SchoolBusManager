<?php
/**
 * Service fournissant une liste des stations sous la forme d'un tableau de la forme :
 *    stationId => commune - nom_station
 * pour value_options d'un select
 *
 * Les méthodes de la classe permettent de filtrer la table selon quelques critères :
 * - visibles : pour les select destinés aux parents
 * - ouvertes : pour les select destinés au service
 * - surcircuit : pour un service donné (millesime, ligneId, sens, moment [, ordre])
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource StationsForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 juil. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Select;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmBase\Model\Session;

class StationsForSelect implements FactoryInterface
{
    use \SbmCommun\Model\Traits\ServiceTrait;

    private $columns;

    private $db_manager;

    private $order;

    private $table_name;

    private $sql;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->table_name = $this->db_manager->getCanonicName('stations', 'vue');
        $this->sql = new Sql($this->db_manager->getDbAdapter());
        $libelle = new Literal('CONCAT(commune, " - ", nom)');
        $this->columns = [
            'stationId',
            'libelle' => $libelle
        ];
        $this->order = [
            'commune',
            'nom'
        ];
        return $this;
    }

    public function toutes()
    {
        $select = $this->sql->select($this->table_name);
        $select->columns($this->columns)->order($this->order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['stationId']] = $row['libelle'];
        }
        return $array;
    }

    public function sauf($stationId)
    {
        $where = new Where();
        $where->notEqualTo('stationId', $stationId);
        $select = $this->sql->select($this->table_name);
        $select->columns($this->columns)
            ->where($where)
            ->order($this->order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['stationId']] = $row['libelle'];
        }
        return $array;
    }

    public function ouvertes()
    {
        $where = new Where();
        $where->literal('ouverte = 1');
        $select = $this->sql->select($this->table_name);
        $select->where($where)
            ->columns($this->columns)
            ->order($this->order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['stationId']] = $row['libelle'];
        }
        return $array;
    }

    public function visibles()
    {
        $where = new Where();
        $where->literal('visible = 1');
        $select = $this->sql->select($this->table_name);
        $select->where($where)
            ->columns($this->columns)
            ->order($this->order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['stationId']] = $row['libelle'];
        }
        return $array;
    }

    /**
     * Afin de présenter les stations dans l'ordre alphabétique (commune - station) il
     * faut trier le tableau selon les libellés. Or si on laisse la clé en numérique le
     * tri est perdu lors du passage de PHP à JS en JSON.
     *
     * @param int $millesime
     * @param string $ligneId
     * @param int $sens
     * @param int $moment
     * @param int $ordre
     * @return array tableau associatif stationId => libelle où stationId est modifiée
     */
    public function surcircuit(int $millesime, string $ligneId, int $sens, int $moment,
        int $ordre = 0)
    {
        $where = new Where();
        $where->equalTo('millesime', $millesime)
            ->equalTo('ligneId', $ligneId)
            ->equalTo('sens', $sens)
            ->equalTo('moment', $moment);
        if ($ordre) {
            $where->equalTo('ordre', $ordre);
        }
        $select = $this->sql->select()
            ->from([
            'sta' => $this->table_name
        ])
            ->columns(
            [
                'stationId',
                'libelle' => new Literal(
                    'CONCAT_WS(" - ", commune, nom, TIME_FORMAT(horaireD, "%H:%i"))')
            ])
            ->join([
            'cir' => $this->db_manager->getCanonicName('circuits', 'table')
        ], 'sta.stationId=cir.stationId', [])
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['stationId']] = $row['libelle'];
        }
        asort($array);
        return $array;
    }

    public function byServiceId(string $serviceId)
    {
        $serviceKeys = $this->decodeServiceId($serviceId);
        return $this->surcircuit(Session::get('millesime'), $serviceKeys['ligneId'],
            $serviceKeys['sens'], $serviceKeys['moment'], $serviceKeys['ordre']);
    }

    /**
     * On renvoie la liste des stations proches du point donné, sur les circuits
     * desservant l'établissement donné. La recherche se fait par pas indiqué jusqu'à une
     * distance maxi indiquée et s'arrête dès que la liste des résultats n'est pas vide.
     *
     * @param \SbmCartographie\Model\Point $point
     * @param string $etablissementId
     * @param int $pas
     *            exprimé en mètres
     * @param int $distanceMaxi
     *            exprimée en mètres
     * @return array
     */
    public function prochesDuPoint($point, $etablissementId, $pas = 2000,
        $distanceMaxi = 10000)
    {
        ; // en mètres
        $limit = 0;
        $array = [];
        $versEtablissement = $this->db_manager->get(
            \SbmCommun\Model\Db\Service\Query\Station\VersEtablissement::class);
        while (empty($array) && $limit <= $distanceMaxi) {
            $limit += $pas;
            $resultset = $versEtablissement->stationsProches($point, $etablissementId,
                $limit);
            foreach ($resultset as $row) {
                $array[$row['stationId']] = $row['commune'] . ' - ' . $row['nom'];
            }
        }
        return $array;
    }
}