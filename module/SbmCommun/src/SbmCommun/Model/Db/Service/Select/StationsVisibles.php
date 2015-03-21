<?php
/**
 * Service fournissant une liste des stations visibles sous la forme d'un tableau
 *   'stationId' => 'commune - nom'
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource StationsVisibles.php
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

class StationsVisibles implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db = $serviceLocator->get('Sbm\Db\DbLib');
        $libelle = new Literal('concat(commune, " - ", nom)');
        $sql = new Sql($db->getDbAdapter());
        $select = $sql->select($db->getCanonicName('stations', 'vue'));
        $select->where('visible = true');
        $select->columns(array('stationId', 'libelle' => $libelle));
        $select->order('commune, nom');
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['stationId']] = $row['libelle'];
        }
        return $array;
    }
}