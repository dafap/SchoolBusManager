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
 * @date 26 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Query\Paiement;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
    protected $db_manager;

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
     * @var array Tableau de la forme [code => libellé, ...]
     */
    protected $modes;

    /**
     * Renvoie la chaine de requête (après l'appel de la requête)
     *
     * @param \Zend\Db\Sql\Select $select
     *
     * @return string
     */
    public function getSqlString($select)
    {
        return $select->getSqlString($this->dbAdapter->getPlatform());
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->millesime = Session::get('millesime');
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
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
        $select = $this->sql->select(
            [
                'p' => $this->db_manager->getCanonicName('paiements', 'table')
            ]);
        $select->columns([
            'anneeScolaire',
            'somme' => new Expression('sum(montant)')
        ])
            ->join(
            [
                'm' => $this->db_manager->getCanonicName('libelles-modes-de-paiement',
                    'vue')
            ], 'm.code=p.codeModeDePaiement', [
                'mode' => 'libelle'
            ])
            ->group([
            'anneeScolaire',
            'libelle'
        ]);

        if (isset($millesime)) {
            $where = new Where();
            $as = $millesime . '-' . ($millesime + 1);
            $where->equalTo('anneeScolaire', $as);
            $select->where($where);
        }

        $statement = $this->sql->prepareStatementForSqlObject($select);
        // $statement->execute() renvoie un \Zend\Db\Adapter\Driver\ResultInterface
        $result = $statement->execute();
        $totalASMode = [];
        $totalAS = [];
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
        return [
            'totalGeneral' => $totalGeneral,
            'totalAS' => $totalAS,
            'totalASMode' => $totalASMode
        ];
    }

    public function getSumByExerciceMode($millesime = null)
    {
        ;
    }
}