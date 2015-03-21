<?php
/**
 * Service fournissant une liste des communes visibles sous la forme d'un tableau
 *   'communeId' => 'nom'
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource CommunesVisibles.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 mars 2015
 * @version 2015-1
 */

namespace SbmCommun\Model\Db\Service\Select; 

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

class CommunesVisibles implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db = $serviceLocator->get('Sbm\Db\DbLib');
        $myDep = new Expression('departement= ?', '12', array(Expression::TYPE_LITERAL));
        $sql = new Sql($db->getDbAdapter());
        $select = $sql->select($db->getCanonicName('communes', 'table'));
        $select->where('visible = true');
        $select->columns(array('communeId', 'nom', 'departement', 'myDep' => $myDep));
        $select->order(array('myDep DESC', 'departement', 'nom'));
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['communeId']] = $row['nom'] . ' (' . $row['departement'] . ')';
        }
        return $array;
    }
}