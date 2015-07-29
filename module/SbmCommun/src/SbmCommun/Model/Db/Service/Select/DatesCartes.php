<?php
/**
 * Service fournissant une liste des dates d'édition des cartes pour le millesime en cours
 *
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource DatesCartes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 juil. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Select;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use DafapSession\Model\Session;
use SbmCommun\Model\DateLib;

class DatesCartes implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $millesime = Session::get('millesime');
        $tCalendar = $serviceLocator->get('Sbm\Db\System\Calendar');
        $dateDebut = $tCalendar->etatDuSite()['dateDebut']->format('Y-m-d');
        $where = new Where();
        $where->greaterThanOrEqualTo('dateCarte', $dateDebut);
        $db = $serviceLocator->get('Sbm\Db\DbLib');
        $sql = new Sql($db->getDbAdapter());
        $select = $sql->select($db->getCanonicName('scolarites', 'table'));
        $select->columns(array(
            'dateCarte'
        ))
            ->order('dateCarte Desc')
            ->quantifier('DISTINCT')
            ->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['dateCarte']] = DateLib::formatDateTimeFromMysql($row['dateCarte']);
        }
        return $array;
    }
} 