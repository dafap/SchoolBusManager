<?php
/**
 * Ensemble de requêtes sur les circuits
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Circuit
 * @filesource Circuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2016
 * @version 2016-2.2.0
 */
namespace SbmCommun\Model\Db\Service\Query\Circuit;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use SbmBase\Model\Session;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class Circuits implements FactoryInterface
{

    /**
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $sm;

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;
    
    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

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

    /**
     * Renvoie la chaine de requête (après l'appel de la requête)
     *
     * @param \Zend\Db\Sql\Select $select
     *
     * @return \Zend\Db\Adapter\mixed
     */
    public function getSqlString($select)
    {
        return $select->getSqlString($this->dbAdapter->getPlatform());
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->sm = $serviceLocator;
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
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
            'cir' => $this->db_manager->getCanonicName('circuits')
        ))
            ->join(array(
            'sta' => $this->db_manager->getCanonicName('stations')
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
            'cir' => $this->db_manager->getCanonicName('circuits')
        ))
            ->join(array(
            'sta' => $this->db_manager->getCanonicName('stations')
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