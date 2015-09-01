<?php
/**
 * Ensemble de requêtes sur les circuits
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Circuit
 * @filesource Circuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 août 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Query\Circuit;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use DafapSession\Model\Session;
use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\SbmCommun\Model\Db;

class Circuits implements FactoryInterface
{

    /**
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $sm;

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbLibService
     */
    private $db;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

    /**
     *
     * @var int
     */
    private $millesime;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->sm = $serviceLocator;
        $this->db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->sql = new Sql($this->db->getDbAdapter());
        $this->millesime = Session::get('millesime');
        return $this;
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
     * Renvoie la description d'un circuit complet de la première station desservie à la dernière.
     * L'ordre des stations dépend de l'horaire demandé : ordre croissant selon m1 le matin, ordre
     * croissant selon s2 le midi ou ordre croissant selon s1 le soir.
     *
     * @param string $serviceId            
     * @param string $horaire
     *            'matin', 'midi' ou 'soir'
     *            
     * @return array
     */
    public function complet($serviceId, $horaire, $callback = null)
    {
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->equalTo('serviceId', $serviceId);
        switch ($horaire) {
            case 'matin':
                $order = 'm1';
                $columns = array(
                    'serviceId',
                    'horaire' => 'm1',
                    'typeArret',
                    'commentaire1'
                );
                break;
            case 'midi':
                $order = 's2';
                $columns = array(
                    'serviceId',
                    'horaire' => new Expression('CONCAT(IFNULL(s2, ""), " - ", IFNULL(s1, ""))'),
                    'typeArret',
                    'commentaire2'
                );
                break;
            case 'soir':
                $order = 's1';
                $columns = array(
                    'serviceId',
                    'horaire' => new Expression('CONCAT(IFNULL(s2, ""), " ", IFNULL(s1, ""))'),
                    'typeArret',
                    'commentaire1'
                );
                break;
            default:
                throw new Exception('L\'horaire demandé est inconnu.');
        }
        $select = $this->sql->select();
        $select->from(array(
            'cir' => $this->db->getCanonicName('circuits')
        ))
            ->join(array(
            'sta' => $this->db->getCanonicName('stations')
        ), 'cir.stationId = sta.stationId', array(
            'stationId' => 'stationId',
            'station' => 'nom'
        ))
            ->columns($columns)
            ->where($where)
            ->order($order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = iterator_to_array($statement->execute());
        if (is_callable($callback)) {
            foreach ($result as &$arret) {
                $arret = $callback($arret);
            }
        }
        return $result;
    }

    /**
     * Renvoie la description d'un circuit de son point de départ jusqu'à l'établissement (matin)
     * ou de l'établissement au point terminus (midi, soir).
     * Le point de départ correspond à l'horaire m1 le plus petit
     * Le point terminus correspond à l'horaire s2 ou s1 le plus grand (midi, soir)
     * Le matin, la section est composée des stations dont l'horaire est compris entre celui du
     * point de départ et celui de la station desservant l'établissement.
     * Le midi et le soir, la section est composée des stations dont l'horaire est compris entre
     * celui de l'établissement et celui du point terminus.
     *
     * @param string $serviceId            
     * @param string $etablissementId            
     * @param string $horaire
     *            'matin', 'midi' ou 'soir'
     *            
     * @return array
     */
    public function section($serviceId, $etablissementId, $horaire)
    {
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->equalTo('serviceId', $serviceId);
        switch ($horaire) {
            case 'matin':
                $where->lessThanOrEqualTo('m1', $this->passageEtablissement($serviceId, $etablissementId, $horaire));
                $order = 'm1 ASC';
                $columns = array(
                    'horaire' => 'm1',
                    'typeArret',
                    'commentaire1'
                );
                break;
            case 'midi':
                $where->greaterThanOrEqualTo('s2', $this->passageEtablissement($serviceId, $etablissementId, $horaire));
                $order = 's2 DESC';
                $columns = array(
                    'horaire' => new Expression('CONCAT(IFNULL(s2, ""), " - ", IFNULL(s1, ""))'),
                    'typeArret',
                    'commentaire2'
                );
                break;
            case 'soir':
                $where->greaterThanOrEqualTo('s1', $this->passageEtablissement($serviceId, $etablissementId, $horaire));
                $order = 's1 DESC';
                $columns = array(
                    'horaire' => new Expression('CONCAT(IFNULL(s2, ""), " - ", IFNULL(s1, ""))'),
                    'typeArret',
                    'commentaire1'
                );
                break;
            default:
                throw new Exception('L\'horaire demandé est inconnu.');
        }
        $select = $this->sql->select();
        $select->from(array(
            'cir' => $this->db->getCanonicName('circuits')
        ))
            ->join(array(
            'sta' => $this->db->getCanonicName('stations')
        ), 'cir.stationId = sta.stationId', array(
            'station' => 'nom'
        ))
            ->columns($columns)
            ->where($where)
            ->order($order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
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
            $oetablissementservice = $this->sm->get('Sbm\Db\Table\EtablissementsServices')->getRecord(array(
                'etablissementId' => $etablissementId,
                'serviceId' => $serviceId
            ));
            return $oetablissementservice->stationId;
        } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
            $code = $e->getCode();
            if (! is_numeric($code)) {
                $code = 0;
            }
            throw new Exception("L'établissement $etablissementId n'est pas desservi par le circuit $serviceId.", $code, $e);
        }
    }

    /**
     * Renvoie l'heure de passage à l'arrêt desservant l'établissement
     *
     * @param string $serviceId            
     * @param string $etablissementId            
     * @param string $horaire
     *            'matin', 'midi' ou 'soir'
     *            
     * @return string heure
     */
    public function passageEtablissement($serviceId, $etablissementId, $horaire)
    {
        $ocircuit = $this->sm->get('Sbm\Db\Table\Circuits')->getRecord(array(
            'millesime' => $this->millesime,
            'serviceId' => $serviceId,
            'stationId' => $this->arretEtablissement($serviceId, $etablissementId)->stationId
        ));
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
                throw new Exception('L\'horaire demandé est inconnu.');
        }
    }
}