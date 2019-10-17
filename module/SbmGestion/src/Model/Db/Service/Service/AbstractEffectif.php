<?php
/**
 * Enregistrement d'un service de calcul d'effectifs
 *
 * Cette classe dérive de AbstractQuery et est dérivée sur l'un des types proposés
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Service
 * @filesource AbstractEffectif.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Service;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use SbmGestion\Model\Db\Service\AbstractQuery;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractEffectif extends AbstractQuery implements FactoryInterface
{

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

    /**
     *
     * @var integer
     */
    protected $millesime;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    protected $sql;

    /**
     * Nom des tables dans la base de données (donné par la méthode
     * DbManager::getCanonicName())
     *
     * @var array
     */
    protected $tableNames;

    /**
     * Tableau structuré des effectifs
     *
     * @var array
     */
    protected $structure;

    public function createService(ServiceLocatorInterface $db_manager)
    {
        if (! ($db_manager instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($db_manager)));
        }
        $this->millesime = Session::get('millesime');
        $this->dbAdapter = $db_manager->getDbAdapter();
        $this->sql = new Sql($db_manager->getDbAdapter());
        foreach ([
            'services'
        ] as $table_name) {
            $this->tableNames[$table_name] = $db_manager->getCanonicName($table_name,
                'table');
        }
        return $this;
    }

    abstract public function getIdColumn();

    public function init(bool $sanspreinscrits = false)
    {
        $this->structure = [];
        $rowset = $this->requete();
        foreach ($rowset as $row) {
            $this->structure[$row[$this->getIdColumn()]] = $row['effectif'];
        }
    }

    public function nbservices($lotId)
    {
        return StdLib::getParam($lotId, $this->structure, 0);
    }

    public function getEffectifColumns()
    {
        return [
            'nbservices' => 'Nombre de services'
        ];
    }

    protected function requete()
    {
        $select = $this->sql->select();
        $select->from([
            's' => $this->tableNames['services']
        ])
            ->columns([
            $this->getIdColumn(),
            'effectif' => new Expression('count(*)')
        ])
            ->group($this->getIdColumn());

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
}