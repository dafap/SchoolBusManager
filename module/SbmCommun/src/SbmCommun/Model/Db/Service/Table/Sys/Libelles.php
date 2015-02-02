<?php
/**
 * Gestion de la table système `libelles
 *
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Service/Table/Sys
 * @filesource Libelles.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 jan 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Table\Sys;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;
use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Literal;

class Libelles extends AbstractSbmTable
{
    /**
     * Initialisation de la classe
     */
    protected function init()
    {
        $this->table_name = 'libelles';
        $this->table_type = 'system';
        $this->table_gateway_alias = 'Sbm\Db\SysTableGateway\Libelles';
        $this->id_name = array('nature', 'code');
    }
    
    public function saveRecordAvecControle(ObjectDataInterface $obj_data, $edit = false, $id = null)
    {
        $nature = $obj_data-> nature;
        $code = $obj_data->code;
        
        $cle = $nature . '|' . $code;
        if ($edit) {
            if ($id == $cle) {
                parent::saveRecord($obj_data);
                $ok = true;
            } elseif ($this->is_newRecord(array('nature' => $nature, 'code' => $code))) {
                list($old_nature, $old_code) = explode('|', $id);
                parent::deleteRecord(array('nature' => $old_nature, 'code' => $old_code));
                parent::saveRecord($obj_data);
                $ok = true;
            } else {
                $ok = false;
            }
        } else {
            if ($this->is_newRecord(array('nature' => $nature, 'code' => $code))) {
                parent::saveRecord($obj_data);
                $ok = true;
            } else {
                $ok = false;
            }
        }
        return $ok;
    }
    
    /**
     * Renvoie tous les libellés ouverts
     * 
     * @return \SbmCommun\Model\Db\Service\Table\ResultSet
     */
    public function fetchOpen()
    {
        $where = new Where(array(new Literal('ouvert = 1')));
        $order = array('nature', 'code');
        return $this->fetchAll($where, $order);
    }
}