<?php
/**
 * Détermine la liste des colonnes d'une table ou d'une requête
 * 
 * @project sbm
 * @package SbmPdf/Model
 * @filesource Columns.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 sept. 2018
 * @version 2016-2.4.5
 */
namespace SbmPdf\Model;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class Columns
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     *
     * @var int
     */
    private $auth_userId;

    /**
     * Source des données : alias d'une vue ou d'une table MySql ou chaine définissant une requête
     * Sql
     *
     * @var string
     */
    private $recordSource;

    /**
     * Indique s'il s'agit d'une table ou vue enregistrée dans le service manager (T) ou une chaine
     * définissant une requête Sql (R)
     *
     * @var string 'T' ou 'R'
     */
    private $recordSourceType;

    public function __construct(ServiceLocatorInterface $db_manager, $auth_userId)
    {
        if (! ($db_manager instanceof DbManager)) {
            $message = 'DbManager attendu. On a reçu un %s.';
            throw new Exception(sprintf($message, gettype($db_manager)));
        }
        $this->db_manager = $db_manager;
        $this->auth_userId = $auth_userId;
    }

    public function setRecordSource($documentId)
    {
        $recordSource = $this->db_manager->get('Sbm\Db\System\Documents')->getRecord(
            $documentId)->recordSource;
        $this->recordSource = $recordSource;
        if (array_key_exists($recordSource, $this->db_manager->getTableAliasList())) {
            // il s'agit d'une table ou d'une vue enregistrée dans le service manager
            $this->recordSourceType = 'T';
        } else {
            // il s'agit d'une chaine définissant une requête Sql
            $this->recordSourceType = 'R';
        }
        return $this;
    }

    /**
     * Renvoie la liste des colonnes d'une table ou d'une requête sous la forme d'un tableau
     * associatif
     *
     * @return array
     */
    public function getListeForSelect()
    {
        if ($this->recordSourceType == 'T') {
            return $this->tableListeDesChamps();
        } else {
            return $this->sqlListeDesChamps();
        }
    }

    protected function tableListeDesChamps()
    {
        $table = $this->db_manager->get($this->recordSource);
        $columns = $this->db_manager->getColumns($table->getTableName(),
            $table->getTableType());
        $result = [];
        foreach ($columns as $ocolumn) {
            $result[$ocolumn->getName()] = $ocolumn->getName() . ' (' .
                $ocolumn->getDataType() . ')';
        }
        return $result;
    }

    protected function sqlListeDesChamps()
    {
        // remplacement des variables éventuelles : %millesime%, %date%, %heure% et %userId%
        $sql = str_replace([
            '%date%',
            '%heure%',
            '%millesime%',
            '%userId%'
        ], [
            date('Y-m-d'),
            date('H:i:s'),
            Session::get('millesime'),
            $this->auth_userId
        ], $this->recordSource);
        $result = [];
        $rowset = $this->db_manager->getDbAdapter()->query($sql,
            \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        if ($rowset->count()) {
            $keys = array_keys($rowset->current()->getArrayCopy());
            $result = array_combine($keys, $keys);
        }
        return $result;
    }
}