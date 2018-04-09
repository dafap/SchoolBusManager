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

class Transporteurs implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $db_manager = $serviceLocator;
        $sql = new Sql($db_manager->getDbAdapter());
        $select = $sql->select($db_manager->getCanonicName('transporteurs', 'vue'));
        $select->columns(
            [
                'transporteurId',
                'nom',
                'commune'
            ]);
        $select->order('nom');
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['transporteurId']] = $row['nom'] . ' (' . $row['commune'] . ')';
        }
        return $array;
    }
}