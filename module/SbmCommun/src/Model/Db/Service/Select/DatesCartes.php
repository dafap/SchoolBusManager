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
 * @date 29 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Select;

use SbmBase\Model\DateLib;
use SbmBase\Model\Session;
use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->millesime = Session::get('millesime');
        $tCalendar = $serviceLocator->get('Sbm\Db\System\Calendar');
        $this->dateDebut = $tCalendar->getEtatDuSite()['dateDebut']->format('Y-m-d');
        return $this;
    }

    public function cartesPapier()
    {
        $where = new Where();
        $where->greaterThanOrEqualTo('dateCarteR1', $this->dateDebut);
        $sql = new Sql($this->db_manager->getDbAdapter());
        $select = $sql->select($this->db_manager->getCanonicName('scolarites', 'table'));
        $select->columns([
            'dateCarteR1'
        ])
            ->order('dateCarteR1 Desc')
            ->quantifier($select::QUANTIFIER_DISTINCT)
            ->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['dateCarteR1']] = DateLib::formatDateTimeFromMysql(
                $row['dateCarteR1']);
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