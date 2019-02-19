<?php
/**
 * Liste des libellés pour une nature donnée
 *
 * @project sbm
 * @package SbmAdmin/Model/Db/Service/Libelle
 * @filesource Liste.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmAdmin\Model\Db\Service\Libelle;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Liste implements FactoryInterface
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
        if (!($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        return $this;
    }

    public function forNature($nature)
    {
        $select = $this->sql->select();
        $select->from([
            'l' => $this->db_manager->getCanonicName('libelles', 'system')
        ])
            ->where([
            'nature' => $nature
        ])
            ->order([
            'code'
        ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
}
 