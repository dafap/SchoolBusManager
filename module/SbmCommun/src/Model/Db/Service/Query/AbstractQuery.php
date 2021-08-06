<?php
/**
 * Quelques méthodes communes à toutes les classes
 *
 *
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Query
 * @filesource AbstractQuery.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 août 2021
 * @version 2021-2.5.14
 */
namespace SbmCommun\Model\Db\Service\Query;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Hydrator\Strategy\StrategyInterface;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractQuery implements FactoryInterface
{
    use \SbmCommun\Model\Traits\SqlStringTrait;

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    protected $db_manager;

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
     * Objet initialisé si nécessaire dans la classe dérivée
     *
     * @var \Zend\Db\Sql\Select
     */
    protected $select;

    /**
     * Tableau de strategy de la forme ['field' => objectStrategy] initialisé si
     * nécessaire dans la méthode init() de la classe dérivée.
     *
     * @var array
     */
    protected $strategies = [];

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->millesime = Session::get('millesime');
        $this->sql = new Sql($this->db_manager->getDbAdapter());
        $this->init();
        return $this;
    }

    abstract protected function init();

    /**
     * Prépare un resultSetPrototype permettant d'appliquer les stratégies au résultat de
     * la requête.
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    protected function getResultSetPrototype()
    {
        $resultSet = new HydratingResultSet();
        $hydrator = $resultSet->getHydrator();
        foreach ($this->strategies as $field => $oStrategy) {
            $hydrator->addStrategy($field, $oStrategy);
        }
        return $resultSet;
    }

    /**
     * Exécute le select passé et rend le résultat après mise en place des strategies
     * paramétrée.
     * Le résultat se comporte comme un iterator de tableaux.
     * Chaque valeur de l'iterator est un objet de type getResultSetPrototype() ou
     * ArrayObject()
     *
     * @param \Zend\Db\Sql\Select $select
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function renderResult(Select $select)
    {
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            return $this->getResultSetPrototype()->initialize($result);
        } else {
            return $result;
        }
    }

    protected function paginator(Select $select)
    {
        return new Paginator(
            new DbSelect($select, $this->db_manager->getDbAdapter(),
                $this->getResultSetPrototype()));
    }

    /**
     * Enregistre une strategy pour le champ indiqué.
     * Elle sera appliquée si on appelle la
     * méthode renderResult() pour obtenir le résultat de la requête.
     *
     * @param string $field
     * @param \Zend\Hydrator\Strategy\StrategyInterface $strategy
     *
     * @return self
     */
    public function addStrategy(string $field, StrategyInterface $strategy): self
    {
        $this->strategies[$field] = $strategy;
        return $this;
    }
}