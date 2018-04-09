<?php
/**
 * Requêtes concernant la table `secteur-scolaires-clg-pu`
 * (déclarée dans module.config.php sous l'alias 'Sbm\Db\Query\SecteursScolairesClgPu')
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Etablissement
 * @filesource SecteursScolairesClgPu.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Service\Query\Etablissement;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Paginator\Adapter\DbSelect;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class SecteursScolairesClgPu implements FactoryInterface
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
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        return $this;
    }

    public function getRecord($id)
    {
        $where = new Where();
        foreach ($id as $key => $value) {
            $where->equalTo($key, $value);
        }
        $select = $this->select($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result->current();
    }

    /**
     * Renvoie un paginator
     *
     * @param \Zend\Db\Sql\Where|array $where            
     * @param string|array $order            
     *
     * @return \SbmCommun\Model\Db\Service\Query\Etablissement\Paginator
     */
    public function paginator($where, $order = [])
    {
        return new Paginator(
            new DbSelect($this->select($where, $order), $this->db_manager->getDbAdapter()));
    }

    private function select($filtre, $order = [])
    {
        $where = new Where();
        $where->literal('eta.niveau = 4')->literal('eta.statut = 1');
        
        $select1 = $this->sql->select();
        $select1->from(
            [
                'ss' => $this->db_manager->getCanonicName('secteurs-scolaires-clg-pu', 
                    'table')
            ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'ss.etablissementId = eta.etablissementId', 
            [
                'etablissement' => 'nom'
            ])
            ->join(
            [
                'cet' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'cet.communeId = eta.communeId', 
            [
                'communeetab' => 'nom'
            ])
            ->join(
            [
                'com' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'ss.communeId = com.communeId', 
            [
                'commune' => 'nom'
            ])
            ->columns(
            [
                'etablissementId',
                'communeId'
            ])
            ->where($where);
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
        // die($this->sql->getSqlStringForSqlObject($select));
        return $select;
    }
}