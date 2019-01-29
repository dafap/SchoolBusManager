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
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Service\Select;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use SbmBase\Model\Session;
use SbmBase\Model\DateLib;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;
use phpDocumentor\Reflection\Types\String_;

class DatesCartes implements FactoryInterface
{
    
    /**
     * 
     * @var string
     */
    private $dateDebut;

    /**
     * 
     * @var DbManager
     */
    private $db_manager;
    
    /**
     * 
     * @var int
     */
    private $millesime;
    
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->millesime = Session::get('millesime');
        $tCalendar = $serviceLocator->get('Sbm\Db\System\Calendar');
        $this->dateDebut = $tCalendar->etatDuSite()['dateDebut']->format('Y-m-d');
        return $this;     
    }
    
    public function cartesPapier()
    {
        $where = new Where();
        $where->greaterThanOrEqualTo('dateCarte', $this->dateDebut);
        $sql = new Sql($this->db_manager->getDbAdapter());
        $select = $sql->select($this->db_manager->getCanonicName('scolarites', 'table'));
        $select->columns([
            'dateCarte'
        ])
        ->order('dateCarte Desc')
        ->quantifier($select::QUANTIFIER_DISTINCT)
        ->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['dateCarte']] = DateLib::formatDateTimeFromMysql(
                $row['dateCarte']);
        }
        return $array;
    }
    
    public function extractionsPhotos()
    {
        $where = new Where();
        $where->greaterThanOrEqualTo('dateExtraction', $this->dateDebut);
        $sql = new Sql($this->db_manager->getDbAdapter());
        $select = $sql->select($this->db_manager->getCanonicName('elevesphotos', 'table'));
        $select->columns([
            'dateExtraction'
        ])
        ->order('dateExtraction Desc')
        ->quantifier($select::QUANTIFIER_DISTINCT)
        ->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['dateExtraction']] = DateLib::formatDateTimeFromMysql(
                $row['dateExtraction']);
        }
        return $array;
    }
} 