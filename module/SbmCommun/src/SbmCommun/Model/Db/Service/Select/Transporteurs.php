<?php
/**
 * Service fournissant une liste des communes desservies sous la forme d'un tableau
 *   'communeId' => 'nom'
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource Transporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 mai 2014
 * @version 2014-1
 */

namespace SbmCommun\Model\Db\Service\Select; //SbmCommun\Model\Db\Service\Select\CommunesDesservies

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

class Transporteurs implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db = $serviceLocator->get('Sbm\Db\DbLib');
        $sql = new Sql($db->getDbAdapter());
        $select = $sql->select($db->getCanonicName('transporteurs', 'vue'));
        $select->columns(array('transporteurId', 'nom', 'commune'));
        $select->order('nom');
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['transporteurId']] = $row['nom'] . ' (' . $row['commune'] . ')';
        }
        return $array;
    }
}