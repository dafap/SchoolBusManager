<?php
/**
 * Requêtes nécessaires au portail des communes
 *
 * @project sbm
 * @package SbmPortail/src/Model/Db/Service/Query
 * @filesource Commune.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 nov. 2020
 * @version 2020-2.6.1
 */
namespace SbmPortail\Model\Db\Service\Query;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Select;
use SbmCartographie\Model\Point;

class Commune extends AbstractQuery
{
    use \SbmCommun\Model\Traits\ExpressionSqlTrait;

    /**
     *
     * @var string
     */
    private $arrayCommuneId;

    private $projection;

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

    public function setProjection($projection): self
    {
        $this->projection = $projection;
        return $this;
    }

    public function paginatorLignes(Where $where = null, array $order = [])
    {
        return $this->paginator($this->selectLignes($where, $order));
    }

    protected function selectLignes(Where $where = null, array $order = [])
    {
        if (is_null($where)) {
            $where = new Where();
        }
        if (! $order) {
            $order = [
                'lig.ligneId'
            ];
        }
        $where->equalTo('lig.millesime', $this->millesime)->in('sta.communeId',
            $this->arrayCommuneId);
        return $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([])
            ->from([
            'sta' => $this->db_manager->getCanonicName('stations')
        ])
            ->join([
            'cir' => $this->db_manager->getCanonicName('circuits')
        ], 'cir.stationId =sta.stationId', [])
            ->join([
            'lig' => $this->db_manager->getCanonicName('lignes')
        ], 'lig.millesime=cir.millesime AND lig.ligneId = cir.ligneId',
            [
                'ligneId',
                'operateur',
                'extremite1',
                'extremite2',
                'via'
            ])
            ->where($where)
            ->order($order);
    }

    public function paginatorCircuits(Where $where, array $order = [])
    {
        $this->addStrategy('semaine',
            $this->db_manager->get('Sbm\Db\Table\Circuits')
                ->getStrategie('semaine'));
        return $this->paginator($this->selectCircuits($where, $order));
    }

    protected function selectCircuits(Where $where = null, array $order = []): Select
    {
        if (is_null($where)) {
            $where = new Where();
        }
        $where->equalTo('cir.millesime', $this->millesime);
        if (! $order) {
            $order = [
                'horaireD',
                'horaireA'
            ];
        }
        return $this->sql->select()
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
    }

    /**
     * Renvoie un tableau de points
     *
     * @return array
     */
    public function stationsPourCarte()
    {
        $resultset = $this->renderResult($this->selectStations());
        $keysService = [
            'serviceId',
            'service',
            'ligneId',
            'sens',
            'moment',
            'ordre',
            'passage',
            'horaireD'
        ];
        $arrayStations = [];
        foreach ($resultset as $row) {
            $row->setFlags(\ArrayObject::ARRAY_AS_PROPS);
            $arStation = $row->getArrayCopy();
            $arService = [];
            foreach ($keysService as $key) {
                $arService[$key] = $row->{$key};
                unset($arStation[$key]);
            }
            $aoService = new \ArrayObject($arService, \ArrayObject::ARRAY_AS_PROPS);
            if (array_key_exists($row->stationId, $arrayStations)) {
                // ajout du service et de l'horaire
                $arrayStations[$row->stationId]->services[] = $aoService;
            } else {
                // création d'un élément
                $arStation['services'][] = $aoService;
                $arrayStations[$row->stationId] = new \ArrayObject($arStation,
                    \ArrayObject::ARRAY_AS_PROPS);
            }
        }
        $ptStations = [];
        foreach ($arrayStations as $station) {
            $station->setFlags(\ArrayObject::ARRAY_AS_PROPS);
            $pt = new Point($station->x, $station->y);
            $pt->setAttribute('station', $station);
            $ptStations[] = $this->projection->xyzVersgRGF93($pt);
        }
        return $ptStations;
    }

    /**
     *
     * @return \Zend\Db\Sql\Select
     */
    protected function selectStations()
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

    /**
     * Renvoie un tableau de points
     *
     * @return array
     */
    public function etablissementsPourCarte()
    {
        $resultset = $this->renderResult($this->selectEtablissements());
        $ptEtablissements = [];
        foreach ($resultset as $etablissement) {
            $etablissement->setFlags(\ArrayObject::ARRAY_AS_PROPS);
            $pt = new Point($etablissement->x, $etablissement->y);
            $pt->setAttribute('etablissement', $etablissement);
            $ptEtablissements[] = $this->projection->xyzVersgRGF93($pt);
        }
        return $ptEtablissements;
    }

    /**
     *
     * @return \Zend\Db\Sql\Select
     */
    protected function selectEtablissements()
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
}