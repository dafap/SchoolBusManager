<?php
/**
 * Requêtes nécessaires au portail des établissements
 *
 * @project sbm
 * @package SbmPortail/src/Model/Db/Service/Query
 * @filesource Etablissement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 nov. 2020
 * @version 2020-2.6.1
 */
namespace SbmPortail\Model\Db\Service\Query;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Select;
use SbmCartographie\Model\Point;

class Etablissement extends AbstractQuery
{
    use \SbmCommun\Model\Traits\ExpressionSqlTrait;

    /**
     *
     * @var string
     */
    private $arrayEtablissementId;

    private $projection;

    private $sansimpayes;

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

    public function setProjection($projection): self
    {
        $this->projection = $projection;
        return $this;
    }

    public function setSansImpayes($sansimpayes): self
    {
        $this->sansimpayes = $sansimpayes;
        return $this;
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
            $result[$row->moment]['options'][$row->moment . '|' . $row->ligneId] = $row->ligneId;
        }
        return $result;
    }

    protected function selectServicesForSelect()
    {
        $where = new Where();
        $where->equalTo('aff.millesime', $this->millesime)->in('sco.etablissementId',
            $this->arrayEtablissementId);
        return $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([
            'moment',
            'ligneId' => 'ligne1Id'
        ])
            ->from([
            'aff' => $this->db_manager->getCanonicName('affectations')
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites')
        ], 'sco.millesime = aff.millesime AND sco.eleveId = aff.eleveId', [])
            ->where($where)
            ->order([
            'moment',
            'ligne1Id'
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

    public function listeEleves(Where $where = null, array $order = [])
    {
        return $this->renderResult($this->selectEleves($where, $order));
    }

    /**
     *
     * @param Where $where
     * @param array $order
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorEleves(Where $where, array $order = [])
    {
        return $this->paginator($this->selectEleves($where, $order));
    }

    protected function selectEleves(Where $where, array $order)
    {
        $where->equalTo('sco.millesime', $this->millesime)->in('sco.etablissementId',
            $this->arrayEtablissementId);
        $subselectAffR1 = $this->sql->select(
            $this->db_manager->getCanonicName('affectations'))
            ->where((new Where())->literal('trajet = 1'));
        $subselectAffR2 = $this->sql->select(
            [
                'aff' => $this->db_manager->getCanonicName('affectations')
            ])
            ->where((new Where())->literal('trajet = 2'));
        $columnR1 = function ($part_adresse) {
            switch ($part_adresse) {
                case 'adresseL1':
                    return 'IFNULL(sco.chez, r1.adresseL1)';
                case 'adresseL2':
                    return 'IFNULL(sco.adresseL1, r1.adresseL2)';
                case 'adresseL3':
                    return 'IFNULL(sco.adresseL2, r1.adresseL3)';
                case 'codePostal':
                    return 'IFNULL(sco.codePostal, r1.codePostal)';
                case 'lacommune':
                    return 'IFNULL(comele.alias, comr1.alias)';
            }
        };
        $select = $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(
            [
                'eleveId',
                'numero',
                'nom_eleve' => 'nom',
                'prenom_eleve' => 'prenom',
                'responsable2Id',
                'adresseL1Elv' => new Literal($columnR1('adresseL1')),
                'adresseL2Elv' => new Literal($columnR1('adresseL2')),
                'adresseL3Elv' => new Literal($columnR1('adresseL3')),
                'codePostalElv' => new Literal($columnR1('codePostal')),
                'lacommuneElv' => new Literal($columnR1('lacommune')),
                'etablissement' => new Literal('CONCAT(eta.nom, " - ", cometa.alias)'),
                'responsable1NomPrenom' => new Literal(
                    'CASE WHEN isnull(r1.responsableId) THEN "" ELSE CONCAT(r1.nom, " ", r1.prenom) END'),
                'responsable2NomPrenom' => new Literal(
                    'CASE WHEN isnull(r2.responsableId) THEN "" ELSE CONCAT(r2.nom, " ", r2.prenom) END')
            ])
            ->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId = sco.eleveId', [
            'inscrit',
            'demandeR1',
            'demandeR2'
        ])
            ->join([
            'aff' => $this->db_manager->getCanonicName('affectations')
        ], 'sco.millesime = aff.millesime AND sco.eleveId = aff.eleveId', [])
            ->join([
            'affR1' => $subselectAffR1
        ], 'sco.millesime = affR1.millesime AND sco.eleveId = affR1.eleveId', [],
            Select::JOIN_LEFT)
            ->join([
            'r1' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'affR1.responsableId = r1.responsableId',
            [
                'adresseL1R1' => 'adresseL1',
                'adresseL2R1' => 'adresseL2',
                'adresseL3R1' => 'adresseL3',
                'codePostalR1' => 'codePostal'
            ], Select::JOIN_LEFT)
            ->join([
            'comr1' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'r1.communeId = comr1.communeId', [
            'lacommuneR1' => 'alias'
        ], Select::JOIN_LEFT)
            ->join([
            'affR2' => $subselectAffR2
        ], 'sco.millesime = affR2.millesime AND sco.eleveId = affR2.eleveId', [],
            Select::JOIN_LEFT)
            ->join([
            'r2' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'affR2.responsableId = r2.responsableId',
            [
                'adresseL1R2' => 'adresseL1',
                'adresseL2R2' => 'adresseL2',
                'adresseL3R2' => 'adresseL3',
                'codePostalR2' => 'codePostal'
            ], Select::JOIN_LEFT)
            ->join([
            'comr2' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'r2.communeId = comr2.communeId', [
            'lacommuneR2' => 'alias'
        ], Select::JOIN_LEFT)
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'sco.etablissementId = eta.etablissementId', [])
            ->join([
            'cometa' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'eta.communeId = cometa.communeId', [])
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'sco.classeId = cla.classeId', [
            'classe' => 'nom'
        ])
            ->join([
            'comele' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'sco.communeId = comele.communeId', [], SELECT::JOIN_LEFT)
            ->where($where)
            ->order($order);
        return $select;
    }

    public function paginatorLignes(Where $where = null, array $order = [])
    {
        return $this->paginator($this->selectLignes($where, $order));
    }

    protected function selectLignes(Where $where = null, array $order = [])
    {
        ;
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
        ;
    }

    /**
     * Renvoie un tableau de points.
     * Stations permettant d'atteindre l'établissement.
     *
     * @return array
     */
    public function stationsPourCarte()
    {
        ;
    }

    /**
     * Renvoie un tableau de points.
     * Etablissements à montrer.
     *
     * @return array
     */
    public function etablissementsPourCarte()
    {
        ;
    }
}