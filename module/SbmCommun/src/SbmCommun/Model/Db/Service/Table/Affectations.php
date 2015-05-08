<?php
/**
 * Gestion de la table `affectations`
 *
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Affectations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 mars 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Table;

use Zend\Db\Sql\Expression;
use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;

class Affectations extends AbstractSbmTable
{

    /**
     * Initialisation du circuit
     */
    protected function init()
    {
        $this->table_name = 'affectations';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Affectations';
        $this->id_name = array(
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'correspondance'
        );
    }
    
    public function insertRecord(ObjectDataInterface $obj_data)
    {
        while (!$this->is_newRecord($obj_data->getId())) {
            $obj_data->correspondance = $obj_data->correspondance + 1;
        }
        
        if (! is_null($this->hydrator)) {
            $data = $this->hydrator->extract($obj_data);
        } else {
            $data = $obj_data->getArrayCopy();
        }
        $this->table_gateway->insert($data);
    }
    
    public function deleteRecord($item)
    {
        if ($item instanceof ObjectDataInterface) {
            $id = $item->getId();
            unset($id['correspondance']);
            parent::deleteRecord($item);
            $resultset =$this->fetchAll($id, 'correspondance');
            $j = 1;
            foreach ($resultset as $obj_data) {
                $array_where = $obj_data->getId();
                $obj_data->correspondance = $j++;
                if (! is_null($this->hydrator)) {
                    $data = $this->hydrator->extract($obj_data);
                } else {
                    $data = $obj_data->getArrayCopy();
                }
                $this->table_gateway->update($data, $array_where);
            }
        }
    }
}