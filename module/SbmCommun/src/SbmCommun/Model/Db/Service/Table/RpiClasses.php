<?php
/**
 * Gestion de la table `rpi-classes`
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Table
 * @filesource RpiClasses.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 août 2018
 * @version 2018-2.4.2
 */
namespace SbmCommun\Model\Db\Service\Table;

use Zend\Db\Sql\Join;

class RpiClasses extends AbstractRpiTable
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'rpi-classes';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\RpiClasses';
        $this->id_name = [
            'classeId',
            'etablissementId'
        ];
    }

    /**
     * Renvoie un tableau
     *
     * @param string $etablissementId            
     * @return array|multitype:
     */
    public function getClasses($etablissementId)
    {
        $t = $this->db_manager->getCanonicName($this->table_name, $this->table_type);
        $select = clone $this->obj_select;
        $select->columns([])
            ->join(
            [
                'cla' => $this->db_manager->getCanonicName('classes', 'table')
            ], "$t.classeId = cla.classeId", 
            [
                'classeId' => 'classeId',
                'nom' => 'nom'
            ])
            ->where($select->where->equalTo('etablissementId', $etablissementId))
            ->order([
            'niveau',
            'rang'
        ]);
        $statement = $this->table_gateway->getSql()->prepareStatementForSqlObject($select);
        try {
            return iterator_to_array($statement->execute());
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Renvoie un tableau
     *
     * @param int $classeId            
     * @return array|multitype:
     */
    public function getEtablissements($classeId)
    {
        $t = $this->db_manager->getCanonicName($this->table_name, $this->table_type);
        $select = clone $this->obj_select;
        $select->columns([])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'vue')
            ], "$t.etablissementId = eta.etablissementId", 
            [
                'etablissementId' => 'etablissementId',
                'nom' => 'nom',
                'commune' => 'commune'
            ])
            ->where($select->where->equalTo('etablissementId', $etablissementId));
        $statement = $this->table_gateway->getSql()->prepareStatementForSqlObject($select);
        try {
            return iterator_to_array($statement->execute());
        } catch (Exception $e) {
            return [];
        }
    }
}