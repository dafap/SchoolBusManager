<?php
/**
 * Service fournissant une liste des services sous la forme d'un tableau
 *   'serviceId' => 'nom'
 * où serviceId est une chaine de la forme ligneId|sens|moment|ordre
 *
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource ServicesForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 mars 2020
 * @version 2020-2.6.0
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
    use \SbmCommun\Model\Traits\ServiceTrait;

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
     * Liste des colonnes utilisées par les méthodes de cette classe
     *
     * @var \Zend\Db\Sql\Literal[]
     */
    private $columns;

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
        $this->columns = $this->getServiceKeys(); // à faire en premier
        $sens = "CASE sens WHEN 1 THEN 'Aller' ELSE 'Retour' END";
        $moment = "CASE moment WHEN 1 THEN 'Matin' WHEN 2 THEN 'Midi' ELSE 'Soir' END";
        $this->columns['libelle'] = new Literal(
            "CONCAT(ligneId, ' - ', $sens, ' - ', $moment, ' - Numéro ', ordre)");
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
        $select = $this->sql->select($this->table_name);
        $select->columns($this->columns)->order($this->getServiceKeys());
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$this->encodeServiceId($row)] = $row['libelle'];
        }
        return $array;
    }

    /**
     * Liste des services desservant un établissement (éventuellement à un moment donné)
     *
     * @param string $etablissementId
     * @param int $moment
     *
     * @return array
     */
    public function desservent(string $etablissementId, int $moment = 0)
    {
        $conditions = [
            'etablissementId' => $etablissementId
        ];
        if ($moment) {
            $conditions['moment'] = $moment;
        }
        $select = $this->sql->select([
            's' => $this->table_name
        ])
            ->columns($this->columns)
            ->join([
            'es' => $this->table_lien
        ], 's.serviceId = es.serviceId', [])
            ->where($conditions)
            ->order($this->getServiceKeys());
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$this->encodeServiceId($row)] = $row['libelle'];
        }
        return $array;
    }
}