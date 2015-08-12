<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource BordereauxForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 août 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Select;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use SbmCommun\Model\DateLib;

class BordereauxForSelect implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbLibService
     */
    private $db;

    private $table_name;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->table_name = $this->db->getCanonicName('paiements', 'vue');
        $this->sql = new Sql($this->db->getDbAdapter());
        return $this;
    }

    public function encours()
    {
        $where = new Where();
        $where->isNotNull('dateBordereau')->isNull('dateDepot');
        $select = $this->sql->select($this->table_name)
            ->columns(array(
            'codeModeDePaiement',
            'modeDePaiement',
            'dateBordereau'
        ))
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            if ($row['modeDePaiement'] == 'chèque') {
                $row['modeDePaiement'] = 'chèques';
            }
            $key = sprintf('%s|%d', $row['dateBordereau'], $row['codeModeDePaiement']);
            $array[$key] = sprintf('bordereau de %s du %s', $row['modeDePaiement'], DateLib::formatDateTimeFromMysql($row['dateBordereau']));
        }
        return $array;
    }

    /**
     * Renvoie un tableau permettant de charger un select pour choisir un bordereau cloturé
     *
     * @return array Tableau de la forme array(key => libelle, ...)
     */
    public function clotures()
    {
        $where = new Where();
        $where->isNotNull('dateDepot')->isNotNull('dateBordereau');
        $select = $this->sql->select($this->table_name)
            ->columns(array(
            'codeModeDePaiement',
            'modeDePaiement',
            'dateBordereau'
        ))
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where($where)
            ->order(array('dateBordereau DESC', 'codeModeDePaiement'));
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            if ($row['modeDePaiement'] == 'chèque') {
                $row['modeDePaiement'] = 'chèques';
            }
            $key = sprintf('%s|%d', $row['dateBordereau'], $row['codeModeDePaiement']);
            $array[$key] = sprintf('bordereau de %s du %s', $row['modeDePaiement'], DateLib::formatDateTimeFromMysql($row['dateBordereau']));
        }
        return $array;
    }

    /**
     * Décode la clé renvoyée par le select clotures()
     *
     * @param string $key
     *            clé à décodée, donnée par l'option sélectionnée dans le select chargé par la méthode clotures()
     *            
     * @return array Tableau array('dateBordereau' => dateMysql, 'codeModeDePaiement' => int)
     */
    public function decode($key)
    {
        $parts = explode('|', $key);
        return array(
            'dateBordereau' => $parts[0],
            'codeModeDePaiement' => $parts[1]
        );
    }
} 