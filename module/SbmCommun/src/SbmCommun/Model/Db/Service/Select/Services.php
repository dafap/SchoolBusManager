<?php
/**
 * Service fournissant une liste des services sous la forme d'un tableau
 *   'serviceId' => 'serviceId - nom (operateur - transporteur)'
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource Services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 avr. 2016
 * @version 2016-2
 */

namespace SbmCommun\Model\Db\Service\Select; 

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Literal;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class Services implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $db_manager = $serviceLocator;
        $libelle = new Literal('concat(serviceId, " - ", nom, " (", operateur, " - ", transporteur, ")")');
        $sql = new Sql($db_manager->getDbAdapter());
        $select = $sql->select($db_manager->getCanonicName('services', 'vue'));
        $select->columns(array('serviceId', 'libelle' => $libelle));
        $select->order('serviceId');
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['serviceId']] = $row['libelle'];
        }
        return $array;
    }
}