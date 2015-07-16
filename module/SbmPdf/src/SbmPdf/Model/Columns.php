<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource Columns.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 juil. 2015
 * @version 2015-1
 */
namespace SbmPdf\Model;

use Zend\ServiceManager\ServiceLocatorInterface;

class Columns
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbLibService
     */
    private $db;

    /**
     * Service manager
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $sm;

    /**
     * Source des données : alias d'une vue ou d'une table MySql ou chaine définissant une requête Sql
     *
     * @var string
     */
    private $recordSource;

    /**
     * Indique s'il s'agit d'une table ou vue enregistrée dans le service manager (T) ou une chaine définissant une requête Sql (R)
     * 
     * @var string 'T' ou 'R'
     */
    private $recordSourceType;

    public function __construct(ServiceLocatorInterface $sm, $recordSource)
    {
        $this->sm = $sm;
        $this->recordSource = $recordSource;
        $this->db = $this->sm->get('Sbm\Db\DbLib');
        if (array_key_exists($recordSource, $this->db->getTableAliasList())) {
            // il s'agit d'une table ou d'une vue enregistrée dans le service manager
            $this->recordSourceType = 'T';
        } else {
            // il s'agit d'une chaine définissant une requête Sql
            $this->recordSourceType = 'R';
        }
    }
    
    /**
     * Renvoie la liste des colonnes d'une table ou d'une requête sous la forme d'un tableau associatif
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
        $table = $this->sm->get($this->recordSource);
        $columns = $this->db->getColumns($table->getTableName(), $table->getTableType());
        $result = array();
        foreach ($columns as $ocolumn) {
            $result[$ocolumn->getName()] = $ocolumn->getName() . ' (' . $ocolumn->getDataType() . ')';
        }
        return $result;
    }
    
    protected function sqlListeDesChamps()
    {
        $sql = $this->recordSource;        
        $result = array();
        $rowset = $this->db->getDbAdapter()->query($this->recordSource, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        if ($rowset->count()) {
            $keys = array_keys($rowset->current()->getArrayCopy());
            $result = array_combine($keys, $keys);
        }
        return $result;
    }
}