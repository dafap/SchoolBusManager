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
 * @date 5 août 2021
 * @version 2021-2.5.14
 */
namespace SbmAdmin\Model\Db\Service\Responsable;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\Exception\ExceptionNoDbManager as Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Responsables implements FactoryInterface
{
    use \SbmCommun\Model\Traits\SqlStringTrait;

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
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectResponsablesSansCompte());
        return $statement->execute();
    }

    protected function selectResponsablesSansCompte()
    {
        $where = new Where();
        $where->isNull('u.userId')->isNotNull('r.email');
        return $this->sql->select(
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
            ->join([
            'u' => $this->db_manager->getCanonicName('users', 'table')
        ], 'u.email = r.email', [], Select::JOIN_LEFT)
            ->where($where);
    }
}