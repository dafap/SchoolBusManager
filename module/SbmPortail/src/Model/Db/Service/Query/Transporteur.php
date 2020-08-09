<?php
/**
 * Requêtes nécessaires au portail des transporteurs
 *
 * @project sbm
 * @package SbmPortail/Model/Db/Service/Query
 * @filesource Transporteur.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 août 2020
 * @version 2020-2.6.0
 */
namespace SbmPortail\Model\Db\Service\Query;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class Transporteur extends AbstractQuery
{
    use \SbmCommun\Model\Traits\ServiceTrait;

    /**
     *
     * @var int
     */
    private $transporteurId;

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
        $this->transporteurId = false;
        $this->serviceId = false;
        $this->stationId = false;
    }

    public function setTransporteurId(int $transporteurId)
    {
        $this->transporteurId = $transporteurId;
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

    public function getScolaritesR($where)
    {
        return $this->renderResult($this->selectScolaritesR($where));
    }

    public function paginator(Where $where)
    {
        return parent::paginator($this->selectScolaritesR($where));
    }

    /**
     * Ici,le millesime est déjà dans le Where
     *
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Db\Sql\Select
     */
    protected function selectScolaritesR(Where $where): Select
    {
        $columns = [
            'eleveId',
            'numero',
            'nomSA',
            'prenomSA',
            'eleve' => new Literal('CONCAT_WS(" ", ele.nom, ele.prenom)'),
            'origine' => new Literal('IF(sub.trajet=1, ori1.nom, IFNULL(ori2.nom,""))'),
            'ga' => new Literal('IF(ISNULL(ele.responsable2Id), "", "GA")'),
            'etablissement' => new Literal('CONCAT_WS(" ", eta.nom, etacom.alias)')
        ];
        $select = $this->sql->select()
            ->columns($columns)
            ->from([
            'ele' => $this->db_manager->getCanonicName('eleves')
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites')
        ], 'ele.eleveId = sco.eleveId',
            [
                'millesime',
                'etablissementId',
                'classeId',
                'inscrit'
            ])
            ->join([
            'eta' => $this->db_manager->getCanonicName('etablissements')
        ], 'eta.etablissementId = sco.etablissementId', [])
            ->join([
            'etacom' => $this->db_manager->getCanonicName('communes')
        ], 'eta.communeId = etacom.communeId', [])
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes')
        ], 'cla.classeId = sco.classeId', [
            'classe' => 'nom'
        ])
            ->join([
            'sub' => $this->subselectScolaritesR()
        ], 'sub.millesime = sco.millesime AND sub.eleveId = sco.eleveId',
            [
                'trajet',
                'responsable',
                'adresseL1',
                'adresseL2',
                'adresseL3',
                'codePostal',
                'commune',
                'telephoneF',
                'telephoneP',
                'telephoneT',
                'email'
            ])
            ->join([
            'ori1' => $this->db_manager->getCanonicName('stations')
        ], 'ori1.stationId = sco.stationIdR1', [])
            ->join([
            'ori2' => $this->db_manager->getCanonicName('stations')
        ], 'ori2.stationId = sco.stationIdR2', [], Select::JOIN_LEFT)
            ->where($where);
        return $select;
    }

    private function subselectScolaritesR(): Select
    {
        $where = new Where();
        $where->equalTo('transporteurId', $this->transporteurId);
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
                'adresseL1',
                'adresseL2',
                'adresseL3',
                'codePostal',
                'telephoneF',
                'telephoneP',
                'telephoneT',
                'email'
            ])
            ->join([
            'subcom' => $this->db_manager->getCanonicName('communes')
        ], 'subres.communeId = subcom.communeId', [
            'commune' => 'alias'
        ])
            ->where($where);
    }

    public function getTelephonesPortables(Where $where)
    {
        return $this->renderResult($this->selectTelephonesPortables($where));
    }

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
        $where->equalTo('transporteurId', $this->transporteurId)
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
}