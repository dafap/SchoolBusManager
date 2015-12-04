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
 * @date 9 nov. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Select;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class EtablissementsForSelect implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbLibService
     */
    private $db;

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
        $this->db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->table_name = $this->db->getCanonicName('etablissements', 'table');
        $this->sql = new Sql($this->db->getDbAdapter());
        return $this;
    }

    public function desservis()
    {
        $select = $this->sql->select($this->db->getCanonicName('etablissements', 'vue'));
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
        $select = $this->sql->select($this->db->getCanonicName('etablissements', 'vue'));
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
        $select = $this->sql->select($this->db->getCanonicName('etablissements', 'vue'));
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