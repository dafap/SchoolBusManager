<?php
/**
 * Service fournissant une liste des communes desservies sous la forme d'un tableau
 *   'communeId' => 'nom'
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource CommunesDesservies.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 mai 2014
 * @version 2014-1
 */

namespace SbmCommun\Model\Db\Service\Select; //SbmCommun\Model\Db\Service\Select\CommunesDesservies

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

class CommunesDesservies implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db = $serviceLocator->get('Sbm\Db\DbLib');
        $d33 = new Expression('departement= ?', '33', array(Expression::TYPE_LITERAL));
        $sql = new Sql($db->getDbAdapter());
        $select = $sql->select($db->getCanonicName('communes', 'table'));
        $select->where('desservie = true');
        $select->columns(array('communeId', 'nom', 'departement', 'd33' => $d33));
        $select->order('d33 DESC, departement, nom');
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['communeId']] = $row['nom'] . ' (' . $row['departement'] . ')';
        }
        return $array;
    }
}