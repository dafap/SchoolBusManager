<?php
/**
 * Service fournissant une liste des classes sous la forme d'un tableau
 *   'classeId' => 'nom'
 * 
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource Classes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Model\Db\Service\Select;

use SbmCommun\Filter\MbUcfirst;
use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Strategy\Niveau;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Classes implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $db_manager = $serviceLocator;
        $oNiveau = new Niveau();
        $mbUcfirst = new MbUcfirst();
        $sql = new Sql($db_manager->getDbAdapter());
        $select = $sql->select($db_manager->getCanonicName('classes', 'table'));
        $select->columns([
            'classeId',
            'nom',
            'niveau'
        ]);
        $select->order([
            'niveau',
            'nom'
        ]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            if (array_key_exists($row['niveau'], $array)) {
                $array[$row['niveau']]['options'][$row['classeId']] = $row['nom'];
            } else {
                $array[$row['niveau']] = [
                    'label' => $mbUcfirst->filter($oNiveau::getNiveaux()[$row['niveau']]),
                    'options' => [
                        $row['classeId'] => $row['nom']
                    ]
                ];
            }
        }
        return $array;
    }
}