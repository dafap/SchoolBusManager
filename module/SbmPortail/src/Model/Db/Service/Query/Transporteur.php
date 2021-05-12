<?php
/**
 * Requêtes nécessaires au portail des transporteurs
 *
 * @project sbm
 * @package SbmPortail/Model/Db/Service/Query
 * @filesource Transporteur.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 mai 2021
 * @version 2021-2.6.1
 */
namespace SbmPortail\Model\Db\Service\Query;

use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class Transporteur extends AbstractPortailQuery
{
    use \SbmCommun\Model\Traits\ServiceTrait, \SbmCommun\Model\Traits\ExpressionSqlTrait;

    /**
     *
     * @var array
     */
    private $arrayTransporteurId;

    /**
     * Encodage des caractéristiques du service (ligneId, sens, moment, ordre) dans une
     * chaine
     *
     * @var string
     */
    private $serviceId;

    /**
     *
     * @var int
     */
    private $stationId;

    protected function init()
    {
        $this->arrayTransporteurId = [];
        $this->serviceId = false;
        $this->stationId = false;
    }

    /**
     *
     * @param array $arrayTransporteurId
     * @return self
     */
    public function setTransporteurId(array $arrayTransporteurId): self
    {
        $this->arrayTransporteurId = $arrayTransporteurId;
        return $this;
    }

    /**
     *
     * @param string $serviceId
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
        return $this;
    }

    /**
     *
     * @param number $stationId
     */
    public function setStationId($stationId)
    {
        $this->stationId = $stationId;
        return $this;
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @param array $order
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getEleves(Where $where, array $order = [])
    {
        return $this->renderResult($this->selectScolaritesR($where, $order));
    }

    /**
     * Condition concernant les transporteurs
     *
     * @param \Zend\Db\Sql\Where $where
     */
    private function adapteWhere(Where $where)
    {
        $where->nest()->in('serR1.transporteurId', $this->arrayTransporteurId)->or->in(
            'serR2.transporteurId', $this->arrayTransporteurId)->unnest();
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmPortail\Model\Db\Service\Query\AbstractPortailQuery::listeEleves()
     */
    public function listeEleves(Where $where = null, array $order = [])
    {
        $this->adapteWhere($where);
        return parent::listeEleves($where, $order);
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @param array $order
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorEleves(Where $where, array $order = [])
    {
        $this->adapteWhere($where);
        return parent::paginatorEleves($where, $order);
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmPortail\Model\Db\Service\Query\AbstractPortailQuery::joinAffectations()
     */
    protected function joinAffectations(Select $select, int $trajet)
    {
        parent::joinAffectations($select, $trajet);
        $aliasService = "serR$trajet";
        $aliasCircuit1 = "cir1R$trajet";
        $select->join([
            $aliasService => $this->db_manager->getCanonicName('services')
        ], $this->jointureService($aliasService, $aliasCircuit1), [], Select::JOIN_LEFT);
        return $this;
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getTelephonesPortables(Where $where)
    {
        return $this->renderResult($this->selectTelephonesPortables($where));
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Db\Sql\Select
     */
    protected function selectTelephonesPortables(Where $where): Select
    {
        $selectFP = $this->subselectTelephonesPortables('telephoneF', 'smsF')->combine(
            $this->subselectTelephonesPortables('telephoneP', 'smsP'));
        $selectFPT = $this->sql->select()
            ->from([
            'fp' => $selectFP
        ])
            ->combine($this->subselectTelephonesPortables('telephoneT', 'smsT'));
        // requête principale
        $columns = [
            'eleve' => new Literal('CONCAT_WS(" ", ele.nom, ele.prenom)'),
            'ga' => new Literal('IF(ISNULL(ele.responsable2Id), "", "GA")')
        ];
        $select = $this->sql->select()
            ->columns($columns)
            ->from([
            'ele' => $this->db_manager->getCanonicName('eleves')
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites')
        ], 'ele.eleveId = sco.eleveId', [])
            ->join([
            'sub' => $selectFPT
        ], 'sub.millesime = sco.millesime AND sub.eleveId = sco.eleveId',
            [
                'responsable',
                'telephone',
                'sms'
            ])
            ->where($where);
        return $select;
    }

    /**
     *
     * @param string $column
     *            'telephoneF' ou 'telephoneP' ou 'telephoneT'
     * @return \Zend\Db\Sql\Select
     */
    private function subselectTelephonesPortables(string $column_tel, string $column_sms): Select
    {
        $where = new Where();
        $where->in('transporteurId', $this->arrayTransporteurId)
            ->nest()
            ->like($column_tel, '06%')->or->like($column_tel, '07%')->unnest();
        if ($this->serviceId) {
            $arrayId = $this->decodeServiceId($this->serviceId);
            $where->equalTo('subaff.ligne1Id', $arrayId['ligneId'])
                ->equalTo('subaff.sensligne1', $arrayId['sens'])
                ->equalTo('subaff.moment', $arrayId['moment'])
                ->equalTo('subaff.ordreligne1', $arrayId['ordre']);
        }
        if ($this->stationId) {
            $where->nest()->equalTo('subaff.station1Id', $this->stationId)->or->equalTo(
                'subaff.station2Id', $this->stationId)->unnest();
        }
        return $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'responsable' => new Literal(
                    'CONCAT_WS(" ", subres.nomSA, subres.prenomSA)')
            ])
            ->from([
            'subaff' => $this->db_manager->getCanonicName('affectations')
        ])
            ->join([
            'subser1' => $this->db_manager->getCanonicName('services')
        ],
            implode(' AND ',
                [
                    'subaff.millesime = subser1.millesime',
                    'subaff.ligne1Id = subser1.ligneId',
                    'subaff.sensligne1 = subser1.sens',
                    'subaff.moment = subser1.moment',
                    'subaff.ordreligne1 = subser1.ordre'
                ]), [])
            ->join([
            'subres' => $this->db_manager->getCanonicName('responsables')
        ], 'subaff.responsableId = subres.responsableId',
            [
                'telephone' => $column_tel,
                'sms' => $column_sms
            ])
            ->where($where);
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmPortail\Model\Db\Service\Query\AbstractPortailQuery::selectEtablissementsPourCarte()
     */
    protected function selectEtablissementsPourCarte(): Select
    {
        return $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([])
            ->from([
            'ser' => $this->db_manager->getCanonicName('services')
        ])
            ->join([
            'aff' => $this->db_manager->getCanonicName('affectations')
        ],
            implode(' AND ',
                [
                    'ser.millesime = aff.millesime',
                    'ser.ligneId = aff.ligne1Id',
                    'ser.sens = aff.sensligne1',
                    'ser.moment = aff.moment',
                    'ser.ordre = aff.ordreligne1'
                ]), [])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ],
            implode(' AND ',
                [
                    'aff.millesime = sco.millesime',
                    'aff.eleveId = sco.eleveId'
                ]), [], Select::JOIN_LEFT)
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
            (new Where())->equalTo('ser.millesime', $this->millesime)
                ->in('ser.transporteurId', $this->arrayTransporteurId));
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmPortail\Model\Db\Service\Query\AbstractPortailQuery::selectStationsPourCarte()
     */
    protected function selectStationsPourCarte(): Select
    {
        return $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
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
                    $this->getSqlSemaineLigneHoraireSens('semaine', 'ligneId', 'horaireD',
                        'sens', 'cir')),
                'ligneId',
                'sens',
                'moment',
                'ordre',
                'passage',
                'horaireD'
            ])
            ->join([
            'ser' => $this->db_manager->getCanonicName('services', 'table')
        ],
            implode(' AND ',
                [
                    'ser.millesime = cir.millesime',
                    'ser.ligneId = cir.ligneId',
                    'ser.sens = cir.sens',
                    'ser.moment = cir.moment',
                    'ser.ordre = cir.ordre'
                ]), [])
            ->where(
            (new Where())->in('ser.transporteurId', $this->arrayTransporteurId)
                ->equalTo('ser.millesime', $this->millesime));
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
    protected function selectLignes(Where $where = null, array $order = [])
    {
        // sous-requête pour filtrer sur l'établissement (par un IN)
        $where1 = new Where();
        $where1->equalTo('millesime', $this->millesime)->in('transporteurId',
            $this->arrayTransporteurId);
        $subselect = $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([
            'ligneId'
        ])
            ->from($this->db_manager->getCanonicName('services'))
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
}