<?php
/**
 * Ensemble de requêtes sur les circuits
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Circuit
 * @filesource Circuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Query\Circuit;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class Circuits extends AbstractQuery
{

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
     * c'est le `circuitId` Sinon, c'est un tableau de la forme ['serviceId' => xxx,
     * 'stationId' => yyy] Dans ce dernier cas, la condition sur le millesime est rajoutée
     * dans la méthode. Il n'y a pas de contrôle pour savoir si le tableau est bien formé.
     *
     * @param int|array $circuitIdOuServiceIdStationId
     *
     * @return array
     */
    public function getHoraires($circuitIdOuServiceIdStationId)
    {
        /*
         * SELECT ser.horaire1, cir.m1, cir.s1, cir.z1, ser.horaires, cir.m2, cir.s2,
         * cir.z2, ser.horaire3, cir.m3, cir.s3, cir.z3 FROM sbm_t_circuits AS cir JOIN
         * sbm_t_services AS ser ON cir.serviceId=ser.serviceId WHERE cir.circuitId =
         * $circuitId
         */
        if (is_integer($circuitIdOuServiceIdStationId)) {
            $conditions = [
                'circuitId' => $circuitIdOuServiceIdStationId
            ];
        } else {
            $conditions = array_merge([
                'millesime' => $this->millesime
            ], $circuitIdOuServiceIdStationId);
        }
        $select = $this->sql->select(
            [
                'cir' => $this->db_manager->getCanonicName('circuits')
            ])
            ->columns([
            'm1',
            's1',
            'z1',
            'm2',
            's2',
            'z2',
            'm3',
            's3',
            'z3'
        ])
            ->join([
            'ser' => $this->db_manager->getCanonicName('services')
        ], 'cir.serviceId=ser.serviceId', [
            'horaire1',
            'horaire2',
            'horaire3'
        ])
            ->where($conditions);
        return current(iterator_to_array($this->renderResult($select)));
    }

    /**
     * Renvoie la description d'un circuit complet de la première station desservie à la
     * dernière. L'ordre des stations dépend de l'horaire demandé : ordre croissant selon
     * m1 le matin, ordre croissant selon s2 le midi ou ordre croissant selon s1 le soir.
     *
     * @param string $serviceId
     * @param string $horaire
     *            'matin', 'midi' ou 'soir'
     * @return array
     */
    public function complet($serviceId, $horaire, $callback = null)
    {
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->equalTo('ser.serviceId', $serviceId);
        switch ($horaire) {
            case 'matin':
                $order = 'm1';
                $columns = [
                    'serviceId',
                    'horaire' => 'm1',
                    'emplacement',
                    'typeArret',
                    'commentaire1'
                ];
                break;
            case 'midi':
                $order = 's2';
                $columns = [
                    'serviceId',
                    'horaire' => new Expression(
                        'CONCAT(IFNULL(s2, ""), " - ", IFNULL(s1, ""))'),
                    'emplacement',
                    'typeArret',
                    'commentaire2'
                ];
                break;
            case 'soir':
                $order = 's1';
                $columns = [
                    'horaire' => new Expression(
                        'CONCAT(IFNULL(s2, ""), " ", IFNULL(s1, ""))'),
                    'emplacement',
                    'typeArret',
                    'commentaire1'
                ];
                break;
            default:
                throw new Exception\DomainException('L\'horaire demandé est inconnu.');
        }
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
            'ser' => $this->db_manager->getCanonicName('services')
        ], 'cir.serviceId = ser. serviceId',
            [
                'serviceId' => 'serviceId',
                'alias' => 'alias',
                'aliasTr' => 'aliasTr',
                'service' => 'nom'
            ])
            ->columns($columns)
            ->where($where)
            ->order($order);
        //$result = ;
        $result = iterator_to_array($this->renderResult($select));
        if (is_callable($callback)) {
            foreach ($result as &$arret) {
                $arret = $callback($arret);
            }
        }
        return $result;
    }

    /**
     * Renvoie la description d'un circuit de son point de départ jusqu'à l'établissement
     * (matin) ou de l'établissement au point terminus (midi, soir). Le point de départ
     * correspond à l'horaire m1 le plus petit Le point terminus correspond à l'horaire s2
     * ou s1 le plus grand (midi, soir) Le matin, la section est composée des stations
     * dont l'horaire est compris entre celui du point de départ et celui de la station
     * desservant l'établissement. Le midi et le soir, la section est composée des
     * stations dont l'horaire est compris entre celui de l'établissement et celui du
     * point terminus.
     *
     * @param string $serviceId
     * @param string $etablissementId
     * @param string $horaire
     *            'matin', 'midi' ou 'soir'
     * @return array
     */
    public function section($serviceId, $etablissementId, $horaire)
    {
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->equalTo('serviceId', $serviceId);
        switch ($horaire) {
            case 'matin':
                $where->lessThanOrEqualTo('m1',
                    $this->passageEtablissement($serviceId, $etablissementId, $horaire));
                $order = 'm1 ASC';
                $columns = [
                    'horaire' => 'm1',
                    'emplacement',
                    'typeArret',
                    'commentaire1'
                ];
                break;
            case 'midi':
                $where->greaterThanOrEqualTo('s2',
                    $this->passageEtablissement($serviceId, $etablissementId, $horaire));
                $order = 's2 DESC';
                $columns = [
                    'horaire' => new Expression(
                        'CONCAT(IFNULL(s2, ""), " - ", IFNULL(s1, ""))'),
                    'emplacement',
                    'typeArret',
                    'commentaire2'
                ];
                break;
            case 'soir':
                $where->greaterThanOrEqualTo('s1',
                    $this->passageEtablissement($serviceId, $etablissementId, $horaire));
                $order = 's1 DESC';
                $columns = [
                    'horaire' => new Expression(
                        'CONCAT(IFNULL(s2, ""), " - ", IFNULL(s1, ""))'),
                    'emplacement',
                    'typeArret',
                    'commentaire1'
                ];
                break;
            default:
                throw new Exception\DomainException('L\'horaire demandé est inconnu.');
        }
        $select = $this->sql->select();
        $select->from([
            'cir' => $this->db_manager->getCanonicName('circuits')
        ])
            ->join([
            'sta' => $this->db_manager->getCanonicName('stations')
        ], 'cir.stationId = sta.stationId', [
            'station' => 'nom'
        ])
            ->columns($columns)
            ->where($where)
            ->order($order);
        return $this->renderResult($select);
    }

    /**
     * Donne le stationId du point d'arrêt à l'établissement de ce circuit
     *
     * @param string $serviceId
     * @param string $etablissementId
     *
     * @return int stationId
     */
    public function arretEtablissement($serviceId, $etablissementId)
    {
        try {
            $oetablissementservice = $this->db_manager->get(
                'Sbm\Db\Table\EtablissementsServices')->getRecord(
                [
                    'etablissementId' => $etablissementId,
                    'serviceId' => $serviceId
                ]);
            return $oetablissementservice->stationId;
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            $code = $e->getCode();
            if (! is_numeric($code)) {
                $code = 0;
            }
            throw new Exception\OutOfBoundsException(
                "L'établissement $etablissementId n'est pas desservi par le circuit $serviceId.",
                $code, $e);
        }
    }

    /**
     * Renvoie l'heure de passage à l'arrêt desservant l'établissement
     *
     * @param string $serviceId
     * @param string $etablissementId
     * @param string $horaire
     *            'matin', 'midi' ou 'soir'
     * @return string heure
     */
    public function passageEtablissement($serviceId, $etablissementId, $horaire)
    {
        $ocircuit = $this->db_manager->get('Sbm\Db\Table\Circuits')->getRecord(
            [
                'millesime' => $this->millesime,
                'serviceId' => $serviceId,
                'stationId' => $this->arretEtablissement($serviceId, $etablissementId)->stationId
            ]);
        switch ($horaire) {
            case 'matin':
                return $ocircuit->m1;
                break;
            case 'midi':
                return $ocircuit->s2;
                break;
            case 'soir':
                return $ocircuit->s1;
            default:
                throw new Exception\DomainException('L\'horaire demandé est inconnu.');
        }
    }
}