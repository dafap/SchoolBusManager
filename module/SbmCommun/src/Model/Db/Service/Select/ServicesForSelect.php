<?php
/**
 * Service fournissant une liste des services sous la forme d'un tableau
 * 'serviceId' => 'nom'
 * où serviceId est une chaine de la forme ligneId|sens|moment|ordre
 *
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource ServicesForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 mai 2022
 * @version 2022-2.6.6
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
        $this->columns['jours'] = new Literal($this->getSqlSemaine());
        $select->columns($this->columns)
            ->where([
            'millesime' => $this->millesime
        ])
            ->order($this->getServiceKeys());
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$this->encodeServiceId($row)] = $row['libelle'] . ' (' . $row['jours'] .
                ')';
        }
        return $array;
    }

    /**
     * Renvoie un tableau structuré Service fournissant une liste des services du matin
     * sous la forme d'un tableau 'serviceId' => 'serviceId - nom (operateur -
     * transporteur)'
     *
     * @return array
     */
    public function matin()
    {
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->literal('moment = 1');
        $select = $this->sql->select($this->table_name);
        $this->columns['libelle'] = new Literal($this->getSqlDesignationService());
        $this->columns['jours'] = new Literal($this->getSqlSemaine());
        $select->columns($this->columns)
            ->where($where)
            ->order($this->getServiceKeys());
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$this->encodeServiceId($row)] = $row['libelle'] . ' (' . $row['jours'] .
                ')';
        }
        return $array;
    }

    /**
     * Renvoie un tableau structuré Service fournissant une liste des services du midi
     * sous la forme d'un tableau 'serviceId' => 'serviceId - nom (operateur -
     * transporteur)'
     *
     * @return array
     */
    public function midi()
    {
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->literal('moment = 2');
        $select = $this->sql->select($this->table_name);
        $this->columns['libelle'] = new Literal($this->getSqlDesignationService());
        $this->columns['jours'] = new Literal($this->getSqlSemaine());
        $select->columns($this->columns)
            ->where($where)
            ->order($this->getServiceKeys());
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$this->encodeServiceId($row)] = $row['libelle'] . ' (' . $row['jours'] .
                ')';
        }
        return $array;
    }

    /**
     * Renvoie un tableau structuré Service fournissant une liste des services du soir
     * sous la forme d'un tableau 'serviceId' => 'serviceId - nom (operateur -
     * transporteur)'
     *
     * @return array
     */
    public function soir()
    {
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->literal('moment = 3');
        $select = $this->sql->select($this->table_name);
        $this->columns['libelle'] = new Literal($this->getSqlDesignationService());
        $this->columns['jours'] = new Literal($this->getSqlSemaine());
        $select->columns($this->columns)
            ->where($where)
            ->order($this->getServiceKeys());
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$this->encodeServiceId($row)] = $row['libelle'] . ' (' . $row['jours'] .
                ')';
        }
        return $array;
    }

    /**
     * Renvoie un tableau structuré Service fournissant une liste des services du mercredi
     * soir sous la forme d'un tableau 'serviceId' => 'serviceId - nom (operateur -
     * transporteur)'
     *
     * @return array
     */
    public function mersoir()
    {
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)
            ->literal('moment = 3')
            ->literal('(semaine & 4) = 4');
        $select = $this->sql->select($this->table_name);
        $this->columns['libelle'] = new Literal($this->getSqlDesignationService());
        $this->columns['jours'] = new Literal($this->getSqlSemaine());
        $select->columns($this->columns)
            ->where($where)
            ->order($this->getServiceKeys());
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$this->encodeServiceId($row)] = $row['libelle'] . ' (' . $row['jours'] .
                ')';
        }
        return $array;
    }

    /**
     * Renvoie un tableau structuré Service fournissant une liste des services sous la
     * forme d'un tableau 'serviceId' => 'serviceId - nom (operateur - transporteur)'
     *
     * @param int|array $transporteurId
     * @return array
     */
    public function par($transporteurId)
    {
        $where = new Where();
        $where->equalTo('millesime', $this->millesime);
        if (is_array($transporteurId)) {
            $where->in('transporteurId', $transporteurId);
        } else {
            $where->equalTo('transporteurId', $transporteurId);
        }
        $select = $this->sql->select($this->table_name);
        $this->columns['libelle'] = new Literal($this->getSqlDesignationService());
        $this->columns['jours'] = new Literal($this->getSqlSemaine());
        $select->columns($this->columns)
            ->where(
            [
                'millesime' => $this->millesime,
                'transporteurId' => $transporteurId
            ])
            ->order($this->getServiceKeys());
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$this->encodeServiceId($row)] = $row['libelle'] . ' (' . $row['jours'] .
                ')';
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
            's.millesime' => $this->millesime,
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
     * paramètres.
     * La requête complexe tient compte des stations jumelles dans le trajet et des
     * stations desservant l'établissement au terminus (pour l'aller) ou au départ (pour
     * le retour).
     * Rappel : il y a retour aux moments 2 et 3, sinon c'est un aller.
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
            ->literal('sta1.stationId = cir1.stationId')
            ->literal('sta2.stationId = cir2.stationId')
            ->lessThan('cir1.horaireD', 'cir2.horaireA', Predicate::TYPE_IDENTIFIER,
            Predicate::TYPE_IDENTIFIER);
        /*
         * SELECT millesime, eleveId, trajet, jours, moment, max(correspondance) AS
         * correspondance, selection, responsableId, station1Id, ligne1Id, sensligne1,
         * ordreligne1, station2Id AS linkId, ligne2Id, sensligne2, ordreligne2
         * FROM `sbm_t_affectations`
         * WHERE millesime = 2020 AND moment NOT IN (2,3)
         * GROUP BY millesime, eleveId, trajet, jours, moment
         */
        $aff1 = new Select($this->db_manager->getCanonicName('affectations', 'table'));
        $aff1->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'moment',
                'correspondance' => new Literal('max(correspondance)'),
                'selection',
                'responsableId',
                'station1Id',
                'ligne1Id',
                'sensligne1',
                'ordreligne1',
                'linkId' => 'station2Id',
                'ligne2Id',
                'sensligne2',
                'ordreligne2'
            ])
            ->where(
            (new Where())->equalTo('millesime', $this->millesime)
                ->notIn('moment', [
                2,
                3
            ]))
            ->group([
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'moment'
        ]);
        /*
         * SELECT DISTINCT et1.stationId AS linkId, et2.stationId
         * FROM `sbm_t_etablissements-stations` AS et1
         * INNER JOIN `sbm_t_etablissements-stations` AS et2 ON et1.etablissementId =
         * et2.etablissementId
         */
        $aff2 = new Select();
        $aff2->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([
            'linkId' => 'stationId'
        ])
            ->from(
            [
                'et1' => $this->db_manager->getCanonicName('etablissements-stations',
                    'table')
            ])
            ->join(
            [
                'et2' => $this->db_manager->getCanonicName('etablissements-stations',
                    'table')
            ], 'et1.etablissementId = et2.etablissementId', [
                'stationId'
            ]);
        /*
         * SELECT millesime, eleveId, trajet, jours, moment, min(correspondance) AS
         * correspondance, selection, responsableId, station1Id AS linkId, ligne1Id,
         * sensligne1, ordreligne1, station2Id, ligne2Id, sensligne2, ordreligne2
         * FROM `sbm_t_affectations`
         * WHERE millesime = 2020 AND moment IN (2,3)
         * GROUP BY millesime, eleveId, trajet, jours, moment
         */
        $aff3 = new Select($this->db_manager->getCanonicName('affectations', 'table'));
        $aff3->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'moment',
                'correspondance' => new Literal('min(correspondance)'),
                'selection',
                'responsableId',
                'linkId' => 'station1Id',
                'ligne1Id',
                'sensligne1',
                'ordreligne1',
                'station2Id',
                'ligne2Id',
                'sensligne2',
                'ordreligne2'
            ])
            ->where(
            (new Where())->equalTo('millesime', $this->millesime)
                ->in('moment', [
                2,
                3
            ]))
            ->group([
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'moment'
        ]);
        /*
         * SELECT DISTINCT et1.stationId AS linkId, et2.stationId
         * FROM `sbm_t_etablissements-stations` AS et1
         * INNER JOIN `sbm_t_etablissements-stations` AS et2 ON et1.etablissementId =
         * et2.etablissementId
         */
        $aff4 = new Select();
        $aff4->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([
            'linkId' => 'stationId'
        ])
            ->from(
            [
                'et1' => $this->db_manager->getCanonicName('etablissements-stations',
                    'table')
            ])
            ->join(
            [
                'et2' => $this->db_manager->getCanonicName('etablissements-stations',
                    'table')
            ], 'et1.etablissementId = et2.etablissementId', [
                'stationId'
            ]);
        $sub_aff_union1 = new Select();
        $sub_aff_union1->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'moment',
                'correspondance',
                'selection',
                'responsableId',
                'station1Id',
                'ligne1Id',
                'sensligne1',
                'ordreligne1',
                'ligne2Id',
                'sensligne2',
                'ordreligne2'
            ])
            ->from([
            'aff1' => $aff1
        ])
            ->join([
            'aff2' => $aff2
        ], 'aff1.linkId = aff2.linkId', [
            'station2Id' => 'aff2.stationId'
        ]);
        $sub_aff_union2 = new Select();
        $sub_aff_union2->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'moment',
                'correspondance',
                'selection',
                'responsableId',
                'ligne1Id',
                'sensligne1',
                'ordreligne1',
                'station2Id',
                'ligne2Id',
                'sensligne2',
                'ordreligne2'
            ])
            ->from([
            'aff3' => $aff3
        ])
            ->join([
            'aff4' => $aff4
        ], 'aff3.linkId = aff4.linkId', [
            'station1Id' => 'aff4.stationId'
        ]);
        $aff_union2 = new Select();
        $aff_union2->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'moment',
                'correspondance',
                'selection',
                'responsableId',
                'station1Id',
                'ligne1Id',
                'sensligne1',
                'ordreligne1',
                'station2Id',
                'ligne2Id',
                'sensligne2',
                'ordreligne2'
            ])->from([
            'tmp2' => $sub_aff_union2
        ]);
        $aff = new Select();
        $aff->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'moment',
                'correspondance',
                'selection',
                'responsableId',
                'station1Id',
                'ligne1Id',
                'sensligne1',
                'ordreligne1',
                'station2Id',
                'ligne2Id',
                'sensligne2',
                'ordreligne2'
            ])
            ->from([
            'tmp1' => $sub_aff_union1
        ])
            ->combine($aff_union2);
        $s1 = new Select();
        $s1->columns([
            'stationId' => 'station1Id',
            'searchId' => 'station2Id'
        ])->from($this->db_manager->getCanonicName('stations-stations', 'table'));
        $s2 = new Select();
        $s2->columns([
            'stationId' => 'station2Id',
            'searchId' => 'station1Id'
        ])->from($this->db_manager->getCanonicName('stations-stations', 'table'));
        $sta_union = new \SbmPdf\Model\Db\Sql\Select();
        $sta_union->columns([
            'stationId' => 'stationId',
            'searchId' => 'stationId'
        ])->from($this->db_manager->getCanonicName('stations', 'table'));
        $sub_sta_union = new \SbmPdf\Model\Db\Sql\Select();
        $sub_sta_union->from([
            'u2' => $s1->combine($s2)
        ]);
        $sta_union->combine($sub_sta_union);
        $select = $this->sql->select([
            'aff' => $aff
        ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([])
            ->join([
            'sta1' => $sta_union
        ], 'sta1.searchId = aff.station1Id', [])
            ->join([
            'sta2' => $sta_union
        ], 'sta2.searchId = aff.station2Id', [])
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
            ->where($where)
            ->order([
            's.ligneId',
            's.sens',
            's.moment',
            's.ordre'
        ]);
        //die($this->getSqlString($select));
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$this->encodeServiceId($row)] = $row['libelle'];
        }
        return $array;
    }
}