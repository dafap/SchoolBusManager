<?php
/**
 * Service fournissant une liste des lots de marché sous la forme
 *   'lotId' => 'marche-lot (transporteur)'
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Select
 * @filesource Lots.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Select;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Lots implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $db_manager = $serviceLocator;
        $sql = new Sql($db_manager->getDbAdapter());
        $select = $sql->select($db_manager->getCanonicName('lots', 'vue'));
        $select->columns([
            'lotId',
            'marche',
            'lot',
            'titulaire'
        ]);
        $select->order([
            'marche',
            'lot'
        ]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['lotId']] = $row['marche'] . ' - ' . $row['lot'] . ' (' .
                $row['titulaire'] . ')';
        }
        return $array;
    }
}