<?php
/**
 * Service fournissant une liste des organismes sous la forme d'un tableau
 *   'organismeId' => 'nom'
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource Organismes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 avr. 2016
 * @version 2016-2
 */

namespace SbmCommun\Model\Db\Service\Select; 

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class Organismes implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $db_manager = $serviceLocator;
        $sql = new Sql($db_manager->getDbAdapter());
        $select = $sql->select($db_manager->getCanonicName('organismes', 'vue'));
        $select->columns(array('organismeId', 'nom', 'commune'));
        $select->order('nom');
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['organismeId']] = $row['nom'] . ' (' . $row['commune'] . ')';
        }
        return $array;
    }
}