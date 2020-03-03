<?php
/**
 * Ensemble de requêtes sur les circuits
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Circuit
 * @filesource Circuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Query\Circuit;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Where;

class Circuits extends AbstractQuery
{
    use \SbmCommun\Model\Traits\ServiceTrait;

    protected function init()
    {
    }

    /**
     * Setter
     *
     * @param int $millesime
     */
    public function setMillesime($millesime)
    {
        $this->millesime = $millesime;
    }

    /**
     * Renvoie un tableau des horaires du circuit indiqué. Si le paramètre est un entier
     * c'est le `circuitId` Sinon, c'est un tableau de la forme ['ligneId' => xxx, 'sens'
     * => ..., 'moment' => ..., 'ordre' => ..., 'stationId' => yyy]. Dans ce dernier cas,
     * la condition sur le millesime est rajoutée dans la méthode. Il n'y a pas de
     * contrôle pour savoir si le tableau est bien formé.
     *
     * @param int|array $circuitIdOuArrayLigneIdSensMomentOrdreStationId
     *
     * @return array
     */
    public function getHoraires($circuitIdOuArrayLigneIdSensMomentOrdreStationId)
    {
        /*
         * SELECT semaine, horaireA, horaireD FROM sbm_t_circuits AS cir WHERE
         * cir.circuitId = $circuitId
         */
        if (is_integer($circuitIdOuArrayLigneIdSensMomentOrdreStationId)) {
            $conditions = [
                'circuitId' => $circuitIdOuArrayLigneIdSensMomentOrdreStationId
            ];
        } else {
            $conditions = array_merge([
                'millesime' => $this->millesime
            ], $circuitIdOuArrayLigneIdSensMomentOrdreStationId);
        }
        $select = $this->sql->select(
            [
                'cir' => $this->db_manager->getCanonicName('circuits')
            ])
            ->columns([
            'semaine',
            'horaireA',
            'horaireD'
        ])
            ->where($conditions);
        return current(iterator_to_array($this->renderResult($select)));
    }

    /**
     * Renvoie la description d'un circuit complet de la première station desservie à la
     * dernière. Les stations sont classées dans l'ordre des horaires croissant.
     *
     * @param string $serviceId
     * @param callable $callback
     *
     * @return array
     */
    public function complet(string $serviceId, $callback = null)
    {
        $service = $this->decodeServiceId($serviceId);

        $where = new Where();
        $where->equalTo('millesime', $this->millesime)
            ->equalTo('ser.ligneId', $service['ligneId'])
            ->equalTo('ser.sens', $service['sens'])->equalTo('ser.moment', $service['moment'])
            ->equalTo('ser.ordre', $service['ordre']);
        $order = 'horaireA';
        $columns = [
            'ligneId',
            'sens',
            'moment',
            'sens',
            'passage',
            'semaine',
            'montee',
            'descente',
            'correspondance',
            'horaire' => 'horaireA',
            'emplacement',
            'typeArret',
            'commentaire1',
            'commentaire2'
        ];
        $select = $this->sql->select();
        $select->from([
            'cir' => $this->db_manager->getCanonicName('circuits')
        ])
            ->join([
            'sta' => $this->db_manager->getCanonicName('stations')
        ], 'cir.stationId = sta.stationId',
            [
                'stationId' => 'stationId',
                'station' => 'nom'
            ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes')
        ], 'cir.serviceId = com. serviceId',
            [
                'commune' => 'nom',
                'lacommune' => 'alias',
                'laposte' => 'alias_laposte'
            ])
            ->columns($columns)
            ->where($where)
            ->order($order);
        $result = iterator_to_array($this->renderResult($select));
        if (is_callable($callback)) {
            foreach ($result as &$arret) {
                $arret = $callback($arret);
            }
        }
        return $result;
    }
}