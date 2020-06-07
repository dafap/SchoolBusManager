<?php
/**
 * Requêtes permettant d'extraire la ou les stations permettant de se rendre à un établissement
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Query/Station
 * @filesource VersEtablissement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Query\Station;

use SbmCartographie\Model\Point;
use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Select;

class VersEtablissement extends AbstractQuery
{

    protected function init()
    {
    }

    /**
     * Donne un SELECT qui cherche toutes les stations desservies par un circuit
     * permettant de se rendre à l'établissement demandé
     *
     * @param string $etablissementId
     * @return \Zend\Db\Sql\Select
     */
    protected function selectStationsVers(string $etablissementId): Select
    {
        return $this->sql->select()
            ->from([
            's' => $this->db_manager->getCanonicName('stations')
        ])
            ->join([
            'l' => $this->db_manager->getCanonicName('communes')
        ], 'l.communeId=s.communeId', [
            'commune' => 'nom',
            'codePostal'
        ])
            ->join([
            'c' => $this->db_manager->getCanonicName('circuits')
        ], 's.stationId = c.stationId', [])
            ->join(
            [
                'e' => $this->db_manager->getCanonicName('etablissements-services')
            ],
            implode(' AND ',
                [
                    'e.millesime = c.millesime',
                    'e.ligneId = c.ligneId',
                    'e.sens = c.sens',
                    'e.moment = c.moment',
                    'e.ordre = c.ordre'
                ]), [])
            ->where(
            [
                's.visible' => 1,
                's.ouverte' => 1,
                'c.millesime' => $this->millesime,
                'c.montee' => 1,
                'e.etablissementId' => $etablissementId
            ]);
    }

    /**
     * Renvoie les stations de montee, ouvertes et visibles, desservant l'établissement
     * donné pour le millesime courant
     *
     * @param string $etablissementId
     */
    public function getStations(string $etablissementId)
    {
        $select = $this->selectStationsVers($etablissementId)
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(
            [
                'stationId' => 'stationId',
                'nom' => 'nom',
                'x' => 'x',
                'y' => 'y',
                'ouverte'
            ]);
        return $this->renderResult($select);
    }

    /**
     * Renvoie les stations situées à moins de $limit du domicile, desservies par un
     * circuit allant à l'établissement scolaire. Le calcul se fait avec les coordonnées
     * cartésiennes en mètres du système de projection utilisé (pas les coordonnées
     * géographiques). On vérifie que l'unité du point est vide.
     *
     * @param \SbmCartographie\Model\Point $pointDomicile
     *            point des coordonnées XY cartésiennes en mètres du domicile (dans le
     *            système de projection utilisé)
     * @param string $etablissementId
     *            identifiant de l'établissement scolaire
     * @param int $limit
     *            distance en mètres
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function stationsProches(Point $pointDomicile, string $etablissementId,
        int $limit)
    {
        if ($pointDomicile->getUnite() != '') {
            $message = 'On veut des coordonnées cartésiennes et on a reçu des coordonnées géographiques.';
            throw new \sbmCommun\Model\Db\Exception\OutOfBoundsException(
                "Coordonnées incorrectes : $message");
        }
        return $this->renderResult(
            $this->selectStationsProches($pointDomicile, $etablissementId, $limit));
    }

    protected function selectStationsProches(Point $pointDomicile, string $etablissementId,
        int $limit)
    {
        // calcul arrondi par excès à 5m près
        $sqlExpressionDistance = sprintf('ceil(sqrt(pow(x - %f, 2)+pow(y - %f, 2))/5)*5',
            $pointDomicile->getX(), $pointDomicile->getY());
        $where = new Where();
        $where->lessThanOrEqualTo($sqlExpressionDistance, $limit);
        $select = $this->selectStationsVers($etablissementId);
        $select->column(
            [
                'stationId' => 'stationId',
                'station' => 'nom',
                'd' => new \Zend\Db\Sql\Expression($sqlExpressionDistance)
            ])
            ->where($where)
            ->order('d');
        return $this->renderResult($select);
    }

    public function stationsAMoinsDe1km(Point $pointDomicile, $etablissementId)
    {
        return $this->stationsProches($pointDomicile, $etablissementId, 1000);
    }

    public function stationsJumelles(int $stationId)
    {
        return $this->renderResult($this->selectStationsJumelles($stationId));
    }

    protected function selectStationsJumelles(int $station1Id)
    {
        $where = new Where(null, Where::COMBINED_BY_OR);
        $where->equalTo('station1Id', $station1Id)->equalTo('station2Id', $station1Id);
        return $this->sql->select()
            ->columns([
            'station1Id',
            'station2Id',
            'temps'
        ])
            ->from([
            'stasta' => $this->db_manager->getCanonicName('stations-stations')
        ])
            ->join([
            'sta1' => $this->db_manager->getCanonicName('stations')
        ], 'stasta.station1Id = sta1.stationId',
            [
                'sta1_nom' => 'nom',
                'sta1_ouverte' => 'ouverte',
                'sta1_visible' => 'visible'
            ])
            ->join([
            'com1' => $this->db_manager->getCanonicName('communes')
        ], 'com1.communeId = sta1.communeId',
            [
                'sta1_commune' => 'nom',
                'sta1_lacommune' => 'alias',
                'sta1_laposte' => 'alias_laposte'
            ])
            ->join([
            'sta2' => $this->db_manager->getCanonicName('stations')
        ], 'stasta.station2Id = sta2.stationId',
            [
                'sta2_nom' => 'nom',
                'sta2_ouverte' => 'ouverte',
                'sta2_visible' => 'visible'
            ])
            ->join([
            'com2' => $this->db_manager->getCanonicName('communes')
        ], 'com2.communeId = sta2.communeId',
            [
                'sta2_commune' => 'nom',
                'sta2_lacommune' => 'alias',
                'sta2_laposte' => 'alias_laposte'
            ])
            ->where($where)
            ->order([
            'com2.nom',
            'sta2.nom'
        ]);
    }
}
