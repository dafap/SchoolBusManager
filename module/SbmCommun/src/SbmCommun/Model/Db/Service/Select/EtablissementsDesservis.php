<?php
/**
 * Service fournissant une liste des établissements desservis sous la forme d'un tableau
 * 'etablissementId' => 'commune - nom'
 *
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource EtablissementsDesservis.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 avr. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Select;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;

class EtablissementsDesservis implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db = $serviceLocator->get('Sbm\Db\DbLib');
        $sql = new Sql($db->getDbAdapter());
        $select = $sql->select($db->getCanonicName('etablissements', 'vue'));
        $select->where('desservie = true');
        $select->columns(array('etablissementId', 'commune', 'nom'));
        $select->order(array('commune', 'nom'));
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['etablissementId']] = $row['commune'] . ' - ' . $row['nom'];
        }
        return $array;
    }
}