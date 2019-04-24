<?php
/**
 * Requêtes permettant d'extraire la ou les stations permettant de se rendre à un établissement
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Query/Station
 * @filesource VersEtablissement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Query\Station;

use SbmCartographie\Model\Point;
use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Where;

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
    private function selectStationsVers(string $etablissementId)
    {
        return $this->sql->select()
            ->from([
            's' => $this->db_manager->getCanonicName('stations')
        ])
            ->join([
            'c' => $this->db_manager->getCanonicName('circuits')
        ], 's.stationId = c.stationId')
            ->join(
            [
                'e' => $this->db_manager->getCanonicName('etablissements-services')
            ], 'c.serviceId = e.serviceId')
            ->where(
            [
                'c.millesime' => $this->millesime,
                'c.montee' => 1,
                'e.etablissementId' => $etablissementId
            ]);
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
        // calcul arrondi par excès à 5m près
        $sqlExpressionDistance = sprintf('ceil(sqrt(pow(x-%f)+pow(y-%f))/5)*5',
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
            ->join([
            'l' => $this->db_manager->getCanonicName('communes')
        ], 'l.communeId=s.communeId', [
            'commune' => 'nom'
        ])
            ->where($where)
            ->order('d');
        return $this->renderResult($select);
    }

    public function stationsAMoinsDe1km(Point $pointDomicile, $etablissementId)
    {
        return $this->stationsProches($pointDomicile, $etablissementId, 1000);
    }
}
