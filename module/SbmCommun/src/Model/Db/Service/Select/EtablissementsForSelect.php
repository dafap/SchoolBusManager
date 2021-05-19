<?php
/**
 * Service fournissant une liste des établissements sous la forme d'un tableau
 *   'etablissementId' => 'aliasCG - nom'
 * où aliasCG correspond à alias (lacommune) sauf pour Chambéry pour lequel c'est AUTRES
 *
 * La liste est ordonnées selon 'commune - nom' (ordre alphabétique)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource EtablissementsForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 mai 2021
 * @version 2021-2.6.2
 */
namespace SbmCommun\Model\Db\Service\Select;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->table_name = $this->db_manager->getCanonicName('etablissements', 'table');
        $this->sql = new Sql($this->db_manager->getDbAdapter());
        return $this;
    }

    /**
     *
     * @return \Zend\Db\Sql\Select
     */
    private function selectEtablissement(): \Zend\Db\Sql\Select
    {
        return $this->sql->select()
            ->columns([
            'etablissementId' => 'etablissementId',
            'nom' => 'nom'
        ])
            ->from([
            'eta' => $this->db_manager->getCanonicName('etablissements')
        ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes')
        ], 'eta.communeId = com.communeId', [
            'commune' => 'aliasCG'
        ])
            ->order([
            'com.aliasCG',
            'eta.nom'
        ]);
    }

    public function tous()
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectEtablissement());
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['etablissementId']] = $row['commune'] . ' - ' . $row['nom'];
        }
        return $array;
    }

    public function desservis()
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectEtablissement()
                ->where('eta.desservie = true'));
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['etablissementId']] = $row['commune'] . ' - ' . $row['nom'];
        }
        return $array;
    }

    public function visibles()
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectEtablissement()
                ->where('eta.visible = true'));
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['etablissementId']] = $row['commune'] . ' - ' . $row['nom'];
        }
        return $array;
    }

    public function visiblesPourParent()
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectEtablissement()
            ->where('eta.visible = true'));
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            if (strpos($row['nom'], 'PRIMAIRE') !== false) {
                $row['nom'] .= ' (MATERNELLE OU ÉLÉMENTAIRE)';
            }
            $array[$row['etablissementId']] = $row['commune'] . ' - ' . $row['nom'];
        }
        return $array;
    }

    public function clgPu()
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectEtablissement()
                ->where('eta.statut = 1 AND eta.niveau = 4'));
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['etablissementId']] = $row['commune'] . ' - ' . $row['nom'];
        }
        return $array;
    }

    public function enRpi()
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectEtablissement()
                ->where('eta.regrPeda = 1 AND eta.niveau <= 3'));
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            $array[$row['etablissementId']] = $row['commune'] . ' - ' . $row['nom'];
        }
        return $array;
    }
}