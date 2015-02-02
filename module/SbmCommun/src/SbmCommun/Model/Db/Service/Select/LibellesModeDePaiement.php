<?php
/**
 * Services fournissant les libellés des modes de paiement sous la forme d'un tableau 
 *   'code' => 'libellé'
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource LibellesCaisse.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 janv. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Select; 

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class LibellesModeDePaiement implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db = $serviceLocator->get('Sbm\Db\DbLib');
        $sql = new Sql($db->getDbAdapter());
        $select = $sql->select($db->getCanonicName('libelles', 'system'));
        $select->columns(array('code', 'libelle'));
        $select->order('code');
        $where = new Where();
        $where->literal("nature = 'ModeDePaiement'");
        $where->AND;
        $where->literal('ouvert = 1');
        $select->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['code']] = $row['libelle'];
        }
        return $array;
    }
}