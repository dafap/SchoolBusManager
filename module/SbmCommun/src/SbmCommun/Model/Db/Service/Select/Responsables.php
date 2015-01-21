<?php
/**
 * Service fournissant une liste des responsables sous la forme d'un tableau
 *  'responsableId' => 'nom prenom'
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource Responsables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 nov. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Select;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

class Responsables implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db = $serviceLocator->get('Sbm\Db\DbLib');
        $sql = new Sql($db->getDbAdapter());
        $select = $sql->select($db->getCanonicName('responsables', 'table'));
        $select->columns(array('responsableId', 'nom', 'prenom'));
        $select->order('nom', 'prenom');
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['responsableId']] = $row['nom'] . ' ' . $row['prenom'];
        }
        return $array;
    }
}
 