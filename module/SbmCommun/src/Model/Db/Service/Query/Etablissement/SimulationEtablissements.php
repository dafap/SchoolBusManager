<?php
/**
 * Requêtes concernant la table `simulation-etablissements`
 * (déclarée dans module.config.php sous l'alias 'Sbm\Db\Query\SimulationEtablissements')
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Etablissement
 * @filesource SimulationEtablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Query\Etablissement;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SimulationEtablissements implements FactoryInterface
{

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
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        return $this;
    }

    /**
     * Renvoie un paginator
     *
     * @param \Zend\Db\Sql\Where|array $where
     * @param string|array $order
     *
     * @return \Zend\Paginator\Paginator
     */
    public function paginator($where, $order = [])
    {
        return new Paginator(
            new DbSelect($this->select($where, $order), $this->db_manager->getDbAdapter()));
    }

    private function select($filtre, $order = [])
    {
        $select1 = $this->sql->select();
        $select1->from(
            [
                'se' => $this->db_manager->getCanonicName('simulation-etablissements',
                    'table')
            ])
            ->join(
            [
                'eta1' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'se.origineId = eta1.etablissementId',
            [
                'etablissementorigine' => 'nom',
                'niveauetaborigine' => 'niveau'
            ])
            ->join([
            'cor' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'cor.communeId = eta1.communeId',
            [
                'communeetaborigineId' => 'communeId',
                'communeetaborigine' => 'nom'
            ])
            ->join(
            [
                'eta2' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'se.suivantId = eta2.etablissementId', [
                'etablissementsuivant' => 'nom'
            ])
            ->join([
            'csu' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'eta2.communeId = csu.communeId', [
            'communeetabsuivant' => 'nom'
        ])
            ->columns([
            'origineId',
            'suivantId'
        ]);
        if (! empty($filtre)) {
            $select = $this->sql->select();
            $select->from([
                'liste' => $select1
            ])
                ->where($filtre)
                ->order($order);
        } else {
            $select = $select1->order($order);
        }
        return $select;
    }

    public function getRecord($id)
    {
        $where = new Where();
        $where->equalTo('origineId', $id);

        $select = $this->select($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result->current();
    }
}