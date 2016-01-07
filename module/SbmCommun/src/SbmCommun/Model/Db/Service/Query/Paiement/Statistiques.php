<?php
/**
 * Requêtes pour les statistiques concernant les paiements
 * (classe déclarée dans mocule.config.php sous l'alias 'Sbm\Statistiques\Paiement')
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Paiement
 * @filesource Statistiques.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 juin 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Query\Paiement;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapSession\Model\Session;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Predicate\Predicate;

class Statistiques implements FactoryInterface
{

    /**
     *
     * @var int
     */
    protected $millesime;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $db;
    
    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    protected $sql;

    /**
     * Modes de paiement
     *
     * @var array Tableau de la forme array(code => libellé, ...)
     */
    protected $modes;

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
        $this->millesime = Session::get('millesime');
        $this->db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->dbAdapter = $this->db->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        // $this->modes = $serviceLocator->get('Sbm\Libelles\ModeDePaiement');
        return $this;
    }

    /**
     * Renvoie un tableau des sommes enregistrées par année scolaire et mode de paiement
     * Si le millesime est donné, une seule année scolaire est renvoyée.
     *
     * SELECT libelle, sum(montant)
     * FROM `sbm_t_paiements` p
     * JOIN `sbm_v_libelles-modes-de-paiement` m ON m.code=p.codeModeDePaiement
     * WHERE anneeScolaire='2014-2015'
     * GROUP BY anneeScolaire, codeModeDePaiement
     *
     * @param int $millesime            
     *
     * @return array
     */
    public function getSumByAsMode($millesime = null)
    {
        $select = $this->sql->select(array(
            'p' => $this->db->getCanonicName('paiements', 'table')
        ));
        $select->columns(array(
            'anneeScolaire',
            'somme' => new Expression('sum(montant)')
        ))
            ->join(array(
            'm' => $this->db->getCanonicName('libelles-modes-de-paiement', 'vue')
        ), 'm.code=p.codeModeDePaiement', array(
            'mode' => 'libelle'
        ))
            ->group(array(
            'anneeScolaire',
            'libelle'
        ));
        
        if (isset($millesime)) {
            $where = new Where();
            $as = $millesime . '-' . ($millesime + 1);
            $where->equalTo('anneeScolaire', $as);
            $select->where($where);
        }
        
        $statement = $this->sql->prepareStatementForSqlObject($select);
        // $statement->execute() renvoie un \Zend\Db\Adapter\Driver\ResultInterface
        $result = $statement->execute();
        $totalASMode = array();
        $totalAS = array();
        $totalGeneral = 0;
        foreach ($result as $row) {
            $totalASMode[$row['anneeScolaire']][$row['mode']] = $row['somme'];
            if (isset($totalAS[$row['anneeScolaire']])) {
                $totalAS[$row['anneeScolaire']] += $row['somme'];
            } else {
                $totalAS[$row['anneeScolaire']] = $row['somme'];
            }
            $totalGeneral += $row['somme'];
        }
        return array('totalGeneral' => $totalGeneral, 'totalAS' => $totalAS, 'totalASMode' => $totalASMode);
    }

    public function getSumByExerciceMode($millesime = null)
    {
        ;
    }
}