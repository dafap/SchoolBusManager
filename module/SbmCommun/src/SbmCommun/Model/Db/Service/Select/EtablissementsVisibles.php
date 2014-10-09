<?php
/**
 * Service fournissant une liste des établissements visibles sous la forme d'un tableau
 * 'etablissementId' => 'commune - nom'
 *
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource EtablissementsVisibles.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Select;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;

class EtablissementsVisibles implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db = $serviceLocator->get('Sbm\Db\DbLib');
        $sql = new Sql($db->getDbAdapter());
        $select = $sql->select($db->getCanonicName('etablissements', 'vue'));
        $select->where('visible = true');
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