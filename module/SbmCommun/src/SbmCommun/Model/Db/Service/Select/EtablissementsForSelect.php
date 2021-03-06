<?php
/**
 * Service fournissant une liste des établissements sous la forme d'un tableau
 *   'etablissementId' => 'commune - nom'
 *   
 * La liste est ordonnées selon 'commune - nom' (ordre alphabétique) 
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource EtablissementsForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 avr. 2016
 * @version 2016-2
 */
namespace SbmCommun\Model\Db\Service\Select;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class EtablissementsForSelect implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

    /**
     *
     * @var string
     */
    private $table_name;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->table_name = $this->db_manager->getCanonicName('etablissements', 'table');
        $this->sql = new Sql($this->db_manager->getDbAdapter());
        return $this;
    }

    public function desservis()
    {
        $select = $this->sql->select($this->db_manager->getCanonicName('etablissements', 'vue'));
        $select->where('desservie = true');
        $select->columns(array(
            'etablissementId',
            'commune',
            'nom'
        ));
        $select->order(array(
            'commune',
            'nom'
        ));
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['etablissementId']] = $row['commune'] . ' - ' . $row['nom'];
        }
        return $array;
    }

    public function visibles()
    {
        $select = $this->sql->select($this->db_manager->getCanonicName('etablissements', 'vue'));
        $select->where('visible = true');
        $select->columns(array(
            'etablissementId',
            'commune',
            'nom'
        ));
        $select->order(array(
            'commune',
            'nom'
        ));
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['etablissementId']] = $row['commune'] . ' - ' . $row['nom'];
        }
        return $array;
    }

    public function clgPu()
    {
        $select = $this->sql->select($this->db_manager->getCanonicName('etablissements', 'vue'));
        $select->where('statut = 1 AND niveau = 4');
        $select->columns(array(
            'etablissementId',
            'commune',
            'nom'
        ));
        $select->order(array(
            'commune',
            'nom'
        ));
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['etablissementId']] = $row['commune'] . ' - ' . $row['nom'];
        }
        return $array;
    }
}