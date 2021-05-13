<?php
/**
 * Requêtes utilisées pour le portail de l'organisateur
 *
 *
 * @project sbm
 * @package SbmPortail/src/Model/Db/Service/Query
 * @filesource Organisateur.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 mai 2021
 * @version 2021-2.6.1
 */
namespace SbmPortail\Model\Db\Service\Query;

use SbmAuthentification\Model\CategoriesInterface;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class Organisateur extends AbstractPortailQuery
{
    use \SbmCommun\Model\Traits\ExpressionSqlTrait;

    /**
     * Rien à faire
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Db\Service\Query\AbstractQuery::init()
     */
    protected function init()
    {
    }

    /**
     * Non utilisé ici
     *
     * {@inheritdoc}
     * @see \SbmPortail\Model\Db\Service\Query\AbstractPortailQuery::selectEtablissementsPourCarte()
     */
    protected function selectEtablissementsPourCarte(): Select
    {
    }

    /**
     * Non utilisé ici
     *
     * {@inheritdoc}
     * @see \SbmPortail\Model\Db\Service\Query\AbstractPortailQuery::selectStationsPourCarte()
     */
    protected function selectStationsPourCarte(): Select
    {
    }

    /**
     * Renvoie la liste des utilisateurs en tant que commune
     *
     * @return array Tableau associatif userId => libelle
     */
    public function enTantQueCommune()
    {
        $where = new Where();
        $where->equalTo('categorieId', CategoriesInterface::COMMUNE_ID)->or->equalTo(
            'categorieId', CategoriesInterface::GR_COMMUNES_ID);
        $resultset = $this->renderResult($this->selectEnTantQue($where));
        $array = [];
        foreach ($resultset as $row) {
            $array[$row['userId']] = $row['libelle'];
        }
        return $array;
    }

    /**
     * Renvoie la liste des utilisateurs en tant que établissement
     *
     * @return array Tableau associatif userId => libelle
     */
    public function enTantQueEtablissement()
    {
        $where = new Where();
        $where->equalTo('categorieId', CategoriesInterface::ETABLISSEMENT_ID)->or->equalTo(
            'categorieId', CategoriesInterface::GR_ETABLISSEMENTS_ID);
        $resultset = $this->renderResult($this->selectEnTantQue($where));
        $array = [];
        foreach ($resultset as $row) {
            $array[$row['userId']] = $row['libelle'];
        }
        return $array;
    }

    /**
     * Renvoie la liste des utilisateurs en tant que transporteur
     *
     * @return array Tableau associatif userId => libelle
     */
    public function enTantQueTransporteur()
    {
        $where = new Where();
        $where->equalTo('categorieId', CategoriesInterface::TRANSPORTEUR_ID)->or->equalTo(
            'categorieId', CategoriesInterface::GR_TRANSPORTEURS_ID);
        $resultset = $this->renderResult($this->selectEnTantQue($where));
        $array = [];
        foreach ($resultset as $row) {
            $array[$row['userId']] = $row['libelle'];
        }
        return $array;
    }

    protected function selectEnTantQue(Where $where)
    {
        $select = $this->sql->select($this->db_manager->getCanonicName('users'))
            ->columns(
            [
                'userId',
                'libelle' => new Literal('CONCAT_WS(" ", titre, prenom, nom)')
            ])
            ->where($where)
            ->order([
            'nom'
        ]);
        return $select;
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
        if (is_null($where)) {
            $where = new Where();
        }
        if (! $order) {
            $order = [
                'ligneId'
            ];
        }
        $where->equalTo('millesime', $this->millesime);
        return $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(
            [
                'ligneId',
                'operateur',
                'extremite1',
                'extremite2',
                'via',
                'internes'
            ])
            ->from($this->db_manager->getCanonicName('lignes'))
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
        $where->equalTo('aff.millesime', $this->millesime);
        return $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(
            [
                'moment',
                'serviceId' => new Literal($this->getSqlEncodeServiceId('', true)),
                'designation' => new Literal(
                    $this->getSqlDesignationService('ligne1Id', 'sensligne1', 'moment',
                        'ordreligne1'))
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
            'ligne1Id',
            'sensligne1',
            'ordreligne1'
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
        $where->equalTo('aff.millesime', $this->millesime);
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