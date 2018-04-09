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
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Service\Select;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\SbmCommun\Model\Db;

class Responsables implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $db_manager = $serviceLocator;
        $sql = new Sql($db_manager->getDbAdapter());
        $select = $sql->select($db_manager->getCanonicName('responsables', 'table'));
        $select->columns(
            [
                'responsableId',
                'nom',
                'prenom'
            ]);
        $select->order('nom', 'prenom');
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['responsableId']] = $row['nom'] . ' ' . $row['prenom'];
        }
        return $array;
    }
}
 