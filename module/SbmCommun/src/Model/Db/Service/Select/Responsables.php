<?php
/**
 * Service fournissant une liste des responsables sous la forme d'un tableau
 * 'responsableId' => 'nom prenom'
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource Responsables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmCommun\Model\Db\Service\Select;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Literal;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Responsables implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $db_manager = $serviceLocator;
        $sql = new Sql($db_manager->getDbAdapter());
        $select = $sql->select(
            [
                'res' => $db_manager->getCanonicName('responsables', 'table')
            ])
            ->columns(
            [
                'responsableId',
                'responsable' => new Literal('CONCAT(res.nom," ",res.prenom," (",com.alias,")")')
            ])
            ->join([
            'com' => $db_manager->getCanonicName('communes', 'table')
        ], 'com.communeId = res.communeId', [])
            ->order('res.nom', 'res.prenom');
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['responsableId']] = $row['responsable'];
        }
        return $array;
    }
}
