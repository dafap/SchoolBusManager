<?php
/**
 * Requêtes sur la table responsables pour ce module
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmAdmin/Model/Db/Service/Responsable
 * @filesource Responsables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmAdmin\Model\Db\Service\Responsable;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use SbmCommun\Model\Db\Service\DbManager;
use SbmBase\Model\Session;

class Responsables implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    protected $db_manager;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

    /**
     *
     * @var int
     */
    protected $millesime;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    protected $sql;

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

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->millesime = Session::get('millesime');
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        return $this;
    }

    public function getResponsablesSansCompte()
    {
        $where = new Where();
        $where->isNull('u.userId')->isNotNull('r.email');
        $select = $this->sql->select(
            [
                'r' => $this->db_manager->getCanonicName('responsables', 'table')
            ])
            ->columns(
            [
                'titre' => 'titre',
                'nom' => 'nom',
                'prenom' => 'prenom',
                'email' => 'email'
            ])
            ->join(
            [
                'u' => $this->db_manager->getCanonicName('users', 'table')
            ], 'u.email = r.email', [], Select::JOIN_LEFT)
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
}