<?php
/**
 * Requêtes sur la table users pour peupler la liste de MailChimp
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmMailChimp/Model/Db/Service
 * @filesource Users.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 avr. 2016
 * @version 2016-2.1
 */
namespace SbmMailChimp\Model\Db\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmCommun\Model\Db\Service\DbManager;
use DafapSession\Model\Session;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Having;
use Zend\Db\Sql\Expression;

class Users implements FactoryInterface
{

    /**
     * Millesime de travail
     *
     * @var int
     */
    private $millesime;

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
     * @var \Zend\Db\Sql\Sql
     */
    protected $sql;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'DbManager attendu. On a reçu %s.';
            throw new Exception(sprintf($message), gettype($serviceLocator));
        }
        $this->millesime = Session::get('millesime');
        $this->db_manager = $serviceLocator;
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        return $this;
    }

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
     * Renvoie les données à transmettre à la liste de MailChimp
     *
     * @param int $limit
     *            Si 0 (par défaut) pas de limit; sinon indiquer la valeur
     *            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getMembersForMailChimpListe($limit = 0)
    {
        $sub_having = new Having();
        $sub_having->isNotNull('r.email');
        $sub_select = $this->sql->select([
            'r' => $this->db_manager->getCanonicName('responsables', 'table')
        ])
            ->columns([
            'email',
            'nbelv' => new Expression('count(s.eleveId)')
        ])
            ->join([
            'e' => $this->db_manager->getCanonicName('eleves', 'table')
        ], 'r.responsableId = e.responsable1Id OR r.responsableId = e.responsable2Id', [])
            ->join([
            's' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'e.eleveId = s.eleveId', [])
            ->where([
            's.millesime' => $this->millesime
        ])
            ->group([
            'r.email'
        ])
            ->having($sub_having);
        //die($this->getSqlString($sub_select));
        $select = $this->sql->select([
            'usr' => $this->db_manager->getCanonicName('users', 'table')
        ])
            ->columns([
            'email_address' => 'email',
            'PRENOM' => 'prenom',
            'NOM' => 'nom',
            'CATEGORIE' => 'categorieId',
            'CONFIRME' => 'confirme'
        ])
            ->join([
            'res' => $sub_select
        ], 'usr.email = res.email', [
            'NBELV' => 'nbelv'
        ]);
        if ($limit) {
            $select->limit($limit);
        }
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
}