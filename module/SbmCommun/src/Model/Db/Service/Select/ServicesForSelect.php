<?php
/**
 * Service fournissant une liste des services sous la forme d'un tableau
 *   'serviceId' => 'nom'
 *
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource ServicesForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Select;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ServicesForSelect implements FactoryInterface
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

    /**
     *
     * @var string
     */
    private $table_lien;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->table_name = $this->db_manager->getCanonicName('services', 'vue');
        $this->table_lien = $this->db_manager->getCanonicName('etablissements-services',
            'table');
        $this->sql = new Sql($this->db_manager->getDbAdapter());
        return $this;
    }

    /**
     * Renvoie un tableau structuré Service fournissant une liste des services sous la
     * forme d'un tableau 'serviceId' => 'serviceId - nom (operateur - transporteur)'
     *
     * @return array
     */
    public function tout()
    {
        $libelle = new Literal(
            'concat(serviceId, " - ", nom, " (", operateur, " - ", transporteur, ")")');
        $select = $this->sql->select($this->table_name);
        $select->columns([
            'serviceId',
            'libelle' => $libelle
        ]);
        $select->order('serviceId');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['serviceId']] = $row['libelle'];
        }
        return $array;
    }

    /**
     *
     * @param string $op
     *            mots parmis '=', '==', '<', '<=', '>', '>=', '<>', 'in', 'between'
     * @param mixed $params
     *            int ou array, selon op
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function to($etablissementId)
    {
        $libelle = new Literal(
            'concat(s.serviceId, " - ", nom, " (", operateur, " - ", transporteur, ")")');
        $select = $this->sql->select([
            's' => $this->table_name
        ]);
        $select->columns([
            'serviceId',
            'libelle' => $libelle
        ])
            ->join([
            'es' => $this->table_lien
        ], 's.serviceId = es.serviceId', [])
            ->where(['etablissementId' => $etablissementId])
            ->order('serviceId');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['serviceId']] = $row['libelle'];
        }
        return $array;
    }
}