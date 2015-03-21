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
 * @date 25 fév. 2015
 * @version 2015-1
 */

namespace SbmCommun\Model\Db\Service\Select; 

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Literal;

class Services implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db = $serviceLocator->get('Sbm\Db\DbLib');
        $libelle = new Literal('concat(serviceId, " - ", nom, " (", operateur, " - ", transporteur, ")")');
        $sql = new Sql($db->getDbAdapter());
        $select = $sql->select($db->getCanonicName('services', 'vue'));
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