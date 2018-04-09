<?php
/**
 * Liste des libellés pour une nature donnée
 *
 * @project sbm
 * @package SbmAdmin/Model/Db/Service/Libelle
 * @filesource Liste.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmAdmin\Model\Db\Service\Libelle;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Where;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

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

    public function forNature($nature)
    {
        $select = $this->sql->select();
        $select->from(
            [
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
 