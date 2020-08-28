<?php
/**
 * Reqêtes nécessaires au portail des communes
 *
 * @project sbm
 * @package SbmPortail/src/Model/Db/Service/Query
 * @filesource Commune.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 août 2020
 * @version 2020-2.6.0
 */
namespace SbmPortail\Model\Db\Service\Query;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Select;
use SbmCartographie\Model\Point;

class Commune extends AbstractQuery
{

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
        if (!$order) {
            $order = ['lig.ligneId'];
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

    /**
     * Renvoie un tableau de points
     *
     * @return array
     */
    public function stationsPourCarte()
    {
        $resultset = $this->renderResult($this->selectStations());
        $ptStations = [];
        foreach ($resultset as $station) {
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
            ->where((new Where())->in('sta.communeId', $this->arrayCommuneId));
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