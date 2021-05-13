<?php
/**
 * Requêtes nécessaires au portail des établissements
 *
 * @project sbm
 * @package SbmPortail/src/Model/Db/Service/Query
 * @filesource Etablissement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 mai 2021
 * @version 2021-2.6.1
 */
namespace SbmPortail\Model\Db\Service\Query;

use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class Etablissement extends AbstractPortailQuery
{
    use \SbmCommun\Model\Traits\ExpressionSqlTrait, \SbmCommun\Model\Traits\ServiceTrait;

    /**
     *
     * @var array
     */
    private $arrayEtablissementId;

    protected function init()
    {
    }

    /**
     *
     * @param array $arrayEtablissementId
     * @return self
     */
    public function setEtablissementId(array $arrayEtablissementId): self
    {
        $this->arrayEtablissementId = $arrayEtablissementId;
        return $this;
    }

    private function adapteWhere(Where $where)
    {
        $where->in('sco.etablissementId', $this->arrayEtablissementId);
    }

    public function listeEleves(Where $where = null, array $order = [])
    {
        $this->adapteWhere($where);
        return parent::listeEleves($where, $order);
    }

    /**
     *
     * @param Where $where
     * @param array $order
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorEleves(Where $where, array $order = [])
    {
        $this->adapteWhere($where);
        return parent::paginatorEleves($where, $order);
    }

    public function getArrayEtablissements()
    {
        $arrayEtablissements = [];
        foreach ($this->renderResult($this->selectEtablissements()) as $row) {
            $row->setFlags(\ArrayObject::ARRAY_AS_PROPS);
            $arrayEtablissements[$row->etablissementId] = $row->etablissement;
        }
        return $arrayEtablissements;
    }

    protected function selectEtablissements()
    {
        return $this->sql->select()
            ->columns(
            [
                'etablissementId',
                'etablissement' => new Literal('concat(com.alias, " - ", eta.nom)')
            ])
            ->from([
            'eta' => $this->db_manager->getCanonicName('etablissements')
        ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes')
        ], 'eta.communeId = com.communeId', [])
            ->where((new Where())->in('etablissementId', $this->arrayEtablissementId));
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
        $where->equalTo('millesime', $this->millesime)->in('etablissementId',
            $this->arrayEtablissementId);
        return $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(
            [
                'moment',
                'serviceId' => new Literal($this->getSqlEncodeServiceId()),
                'designation' => new Literal($this->getSqlDesignationService())
            ])
            ->from(
            [
                'etaser' => $this->db_manager->getCanonicName('etablissements-services')
            ])
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
        $where->equalTo('aff.millesime', $this->millesime)->in('sco.etablissementId',
            $this->arrayEtablissementId);
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
        $where1->equalTo('millesime', $this->millesime)->in('etablissementId',
            $this->arrayEtablissementId);
        $subselect = $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([
            'ligneId'
        ])
            ->from($this->db_manager->getCanonicName('etablissements-services'))
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

    protected function selectEtablissementsPourCarte(): Select
    {
        return $this->sql->select()
            ->columns(
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
            ->from([
            'eta' => $this->db_manager->getCanonicName('etablissements')
        ])
            ->join([
            'cometa' => $this->db_manager->getCanonicName('communes')
        ], 'cometa.communeId = eta.communeId',
            [
                'commune' => 'nom',
                'lacommune' => 'alias',
                'laposte' => 'alias_laposte'
            ])
            ->where((new Where())->in('eta.etablissementId', $this->arrayEtablissementId));
    }

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
                    $this->getSqlSemaineLigneHoraireSens('semaine', 'ligneId', 'horaireD')),
                'ligneId',
                'sens',
                'moment',
                'ordre',
                'passage',
                'horaireD'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ], 'aff.station1Id = sta.stationId', [])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'sco.millesime = aff.millesime AND sco.eleveId = aff.eleveId', [])
            ->where(
            (new Where())->in('sco.etablissementId', $this->arrayEtablissementId)
                ->equalTo('sco.millesime', $this->millesime));
    }
}