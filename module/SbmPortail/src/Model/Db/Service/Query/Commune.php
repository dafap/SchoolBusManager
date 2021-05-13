<?php
/**
 * Requêtes nécessaires au portail des communes
 *
 * @project sbm
 * @package SbmPortail/src/Model/Db/Service/Query
 * @filesource Commune.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 mai 2021
 * @version 2021-2.6.1
 */
namespace SbmPortail\Model\Db\Service\Query;

use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class Commune extends AbstractPortailQuery
{
    use \SbmCommun\Model\Traits\ExpressionSqlTrait;

    /**
     *
     * @var array
     */
    private $arrayCommuneId;

    protected function init()
    {
    }

    /**
     *
     * @param array $arrayCommuneId
     * @return self
     */
    public function setCommuneId(array $arrayCommuneId): self
    {
        $this->arrayCommuneId = $arrayCommuneId;
        return $this;
    }

    private function adapteWhere(Where $where)
    {
        $where->nest()->in('r1.communeId', $this->arrayCommuneId)->or->in('r2.communeId',
            $this->arrayCommuneId)->or->in('sco.communeId', $this->arrayCommuneId)->unnest();
    }

    public function listeEleves(Where $where = null, array $order = [])
    {
        $this->adapteWhere($where);
        return parent::listeEleves($where, $order);
    }

    public function paginatorEleves(Where $where = null, array $order = [])
    {
        $this->adapteWhere($where);
        return parent::paginatorEleves($where, $order);
    }

    public function getArrayCommunes()
    {
        $arrayCommunes = [];
        foreach ($this->renderResult($this->selectCommunes()) as $row) {
            $row->setFlags(\ArrayObject::ARRAY_AS_PROPS);
            $arrayCommunes[$row->communeId] = $row->commune;
        }
        return $arrayCommunes;
    }

    protected function selectCommunes()
    {
        return $this->sql->select()
            ->columns(
            [
                'communeId',
                'commune' => new Literal('CONCAT(com.codePostal, " ", com.alias)')
            ])
            ->from([
            'com' => $this->db_manager->getCanonicName('communes')
        ])
            ->where((new Where())->in('communeId', $this->arrayCommuneId));
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @param array $order
     */
    public function listeLignes(Where $where = null, array $order = [])
    {
        return $this->renderResult($this->selectLignes($where, $order));
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @param array $order
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorLignes(Where $where = null, array $order = [])
    {
        return $this->paginator($this->selectLignes($where, $order));
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @param array $order
     * @return \Zend\Db\Sql\Select
     */
    protected function selectLignes(Where $where = null, array $order = []): Select
    {
        // sous-requête pour filtrer sur les communes (par un IN)
        $where1 = new Where();
        $where1->equalTo('millesime', $this->millesime)->in('communeId',
            $this->arrayCommuneId);
        $subselect = $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([
            'ligneId'
        ])
            ->from([
            'cir' => $this->db_manager->getCanonicName('circuits')
        ])
            ->join([
            'sta' => $this->db_manager->getCanonicName('stations')
        ], 'sta.stationId = cir.stationId', [])
            ->where($where1);
        // requête principale
        if (is_null($where)) {
            $where = new Where();
        }
        $where->equalTo('millesime', $this->millesime)->in('ligneId', $subselect);
        if (! $order) {
            $order = [
                'ligneId'
            ];
        }
        return $this->sql->select($this->db_manager->getCanonicName('lignes'))
            ->columns(
            [
                'ligneId',
                'operateur',
                'extremite1',
                'extremite2',
                'via',
                'internes'
            ])
            ->where($where)
            ->order($order);
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @param array $order
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function listeCircuits(Where $where = null, array $order = [])
    {
        return $this->renderResult($this->selectCircuits($where, $order));
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @param array $order
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorCircuits(Where $where, array $order = [])
    {
        $this->addStrategy('semaine',
            $this->db_manager->get('Sbm\Db\Table\Circuits')
                ->getStrategie('semaine'));
        return $this->paginator($this->selectCircuits($where, $order));
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @param array $order
     * @return \Zend\Db\Sql\Select
     */
    protected function selectCircuits(Where $having = null, array $order = []): Select
    {
        $where = new Where();
        $where->equalTo('cir.millesime', $this->millesime);
        if (! $order) {
            $order = [
                'horaireD',
                'horaireA'
            ];
        }
        $select = $this->sql->select()
            ->columns(
            [
                'station' => 'nom',
                'stationAlias' => 'alias',
                'communeIdStation' => 'communeId',
                'stationOuverte' => 'ouverte'
            ])
            ->from([
            'sta' => $this->db_manager->getCanonicName('stations')
        ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes')
        ], 'com.communeId = sta.communeId',
            [
                'communeStation' => 'nom',
                'lacommuneStation' => 'alias',
                'laposteStation' => 'alias_laposte'
            ])
            ->join([
            'cir' => $this->db_manager->getCanonicName('circuits')
        ], 'cir.stationId = sta.stationId',
            [
                'circuitId',
                'ligneId',
                'sens',
                'moment',
                'ordre',
                'passage',
                'horaireA',
                'horaireD',
                'semaine',
                'stationId',
                'emplacement',
                'circuitOuvert' => 'ouvert'
            ])
            ->where($where)
            ->order($order);
        if ($having) {
            $select->having($having);
        }
        return $select;
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmPortail\Model\Db\Service\Query\AbstractPortailQuery::selectEtablissementsPourCarte()
     */
    protected function selectEtablissementsPourCarte(): Select
    {
        return $this->sql->select()
            ->columns([])
            ->from([
            'com' => $this->db_manager->getCanonicName('communes')
        ])
            ->join([
            'res' => $this->db_manager->getCanonicName('responsables')
        ], 'res.communeId = com.communeId', [])
            ->join([
            'ele' => $this->db_manager->getCanonicName('eleves')
        ],
            'ele.responsable1Id = res.responsableId OR ele.responsable2Id = res.responsableId',
            [])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites')
        ], 'sco.eleveId = ele.eleveId', [])
            ->join([
            'eta' => $this->db_manager->getCanonicName('etablissements')
        ], 'eta.etablissementId = sco.etablissementId',
            [
                'nom',
                'adresse1',
                'adresse2',
                'codePostal',
                'telephone',
                'email',
                'niveau',
                'desservie',
                'x',
                'y'
            ])
            ->join([
            'cometa' => $this->db_manager->getCanonicName('communes')
        ], 'cometa.communeId = eta.communeId',
            [
                'commune' => 'nom',
                'lacommune' => 'alias',
                'laposte' => 'alias_laposte'
            ])
            ->where(
            (new Where())->equalTo('millesime', $this->millesime)
                ->in('com.communeId', $this->arrayCommuneId));
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmPortail\Model\Db\Service\Query\AbstractPortailQuery::selectStationsPourCarte()
     */
    protected function selectStationsPourCarte(): Select
    {
        return $this->sql->select()
            ->columns([
            'stationId',
            'x',
            'y',
            'nom',
            'alias',
            'ouverte'
        ])
            ->from([
            'sta' => $this->db_manager->getCanonicName('stations')
        ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes')
        ], 'com.communeId = sta.communeId',
            [
                'codePostal',
                'commune' => 'nom',
                'lacommune' => 'alias',
                'laposte' => 'alias_laposte'
            ])
            ->join([
            'cir' => $this->db_manager->getCanonicName('circuits', 'table')
        ], 'cir.stationId = sta.stationId',
            [
                'serviceId' => new Literal($this->getSqlEncodeServiceId('cir')),
                'service' => new Literal(
                    $this->getSqlSemaineLigneHoraireSens('semaine', 'ligneId', 'horaireD')),
                'ligneId',
                'sens',
                'moment',
                'ordre',
                'passage',
                'horaireD'
            ], Select::JOIN_LEFT)
            ->where(
            (new Where())->in('sta.communeId', $this->arrayCommuneId)
                ->nest()
                ->isNull('millesime')->or->equalTo('millesime', $this->millesime)
                ->unnest());
    }

    public function listeServicesForSelect()
    {
        $libelle = [
            'Matin',
            'Midi',
            'Soir',
            'Après-midi',
            'Dimanche soir'
        ];
        $result = [];
        foreach ($this->renderResult($this->selectServicesForSelect()) as $row) {
            $row->setFlags(\ArrayObject::ARRAY_AS_PROPS);
            if (! array_key_exists($row->moment, $result)) {
                $result[$row->moment]['label'] = $libelle[$row->moment - 1];
            }
            $result[$row->moment]['options'][$row->serviceId] = $row->designation;
        }
        return $result;
    }

    protected function selectServicesForSelect()
    {
        $where = new Where();
        $where->equalTo('cir.millesime', $this->millesime)->in('sta.communeId',
            $this->arrayCommuneId);
        return $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(
            [
                'moment',
                'serviceId' => new Literal($this->getSqlEncodeServiceId()),
                'designation' => new Literal($this->getSqlDesignationService())
            ])
            ->from([
            'cir' => $this->db_manager->getCanonicName('circuits')
        ])
            ->join([
            'sta' => $this->db_manager->getCanonicName('stations')
        ], 'cir.stationId = sta.stationId', [])
            ->where($where)
            ->order([
            'moment',
            'ligneId'
        ]);
    }

    public function listeStationsForSelect()
    {
        $result = [];
        foreach ($this->renderResult($this->selectStationsForSelect()) as $row) {
            $row->setFlags(\ArrayObject::ARRAY_AS_PROPS);
            if (! array_key_exists($row->communeId, $result)) {
                $result[$row->communeId]['label'] = $row->lacommune;
            }
            $result[$row->communeId]['options'][$row->stationId] = $row->station;
        }
        // echo '<pre>';die(var_dump($result));
        return $result;
    }

    protected function selectStationsForSelect()
    {
        $where = new Where();
        $where->equalTo('aff.millesime', $this->millesime)->in('sta.communeId',
            $this->arrayCommuneId);
        return $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([
            'stationId',
            'communeId',
            'station' => 'nom'
        ])
            ->from([
            'sta' => $this->db_manager->getCanonicName('stations')
        ])
            ->join([
            'comsta' => $this->db_manager->getCanonicName('communes')
        ], 'sta.communeId = comsta.communeId', [
            'lacommune' => 'alias'
        ])
            ->join([
            'aff' => $this->db_manager->getCanonicName('affectations')
        ], 'sta.stationId = aff.station1Id OR sta.stationId = aff.station2Id', [])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites')
        ], 'sco.millesime = aff.millesime AND sco.eleveId = aff.eleveId', [])
            ->where($where)
            ->order([
            'comsta.alias',
            'sta.nom'
        ]);
    }
}