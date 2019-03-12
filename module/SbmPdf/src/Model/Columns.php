<?php
/**
 * Détermine la liste des colonnes d'une table ou d'une requête
 *
 * @project sbm
 * @package SbmPdf/Model
 * @filesource Columns.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmPdf\Model;

use SbmCommun\Model\Db\Service\DbManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class Columns
{
    use QuerySourceTrait;

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

    /**
     * Reçoit le nom d'enregistrement d'une classe dérivée de SbmAbstractClass (SbmCommun) et
     * renvoie le nom d'enregistrement d'un EffectifInterface (SbmGestion)
     *
     * @param string $stringSbmAbstractTable
     *
     * @return string
     *
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable
     * @see \SbmGestion\Model\Db\Service\Eleve\EffectifInterface
     */
    public static function getStringEffectifInterface($stringSbmAbstractTable)
    {
        $parts = explode('\\', $stringSbmAbstractTable);
        $parts[2] = 'Eleve';
        $parts[3] = 'Effectif' . $parts[3];
        return implode('\\', $parts);
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
        if ($table instanceof \SbmCommun\Model\Db\Service\Table\EffectifInterface) {
            $effetifTable = self::getStringEffectifInterface($this->recordSource);
            if ($this->db_manager->has($effetifTable)) {
                foreach ($this->db_manager->get($effetifTable)->getEffectifColumns() as $key => $value) {
                    $result["%$key%"] = $value;
                }
            }
        }
        return $result;
    }

    protected function sqlListeDesChamps()
    {
        // remplacement des variables éventuelles : %millesime%, %date%, %heure% et %userId%
        // et des opérateurs %gt%, %gtOrEq%, %lt%, %ltOrEq%, %ltgt%, %notEq%
        $sql = $this->decodeSource($this->recordSource, $this->auth_userId);
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