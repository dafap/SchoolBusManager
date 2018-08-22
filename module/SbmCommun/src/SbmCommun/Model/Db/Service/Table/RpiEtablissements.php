<?php
/**
 * Gestion de la table `rpi-etablissements`
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Table
 * @filesource RpiEtablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 août 2018
 * @version 2018-2.4.2
 */
namespace SbmCommun\Model\Db\Service\Table;

class RpiEtablissements extends AbstractRpiTable
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'rpi-etablissements';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\RpiEtablissements';
        $this->id_name = [
            'rpiId',
            'etablissementId'
        ];
    }

    public function getRpiId($etablissementId)
    {
        $resultset = $this->fetchAll(
            [
                'etablissementId' => $etablissementId
            ]);
        if ($resultset->count()) {
            return $resultset->current()->rpiId;
        } else {
            throw new Exception(
                sprintf(
                    'L\'établissement %s n\'est pas dans la table `rpi-etablissements`.', 
                    $etablissementId));
        }
    }

    /**
     * Renvoie un objet.
     * Cela permet d'y adjoindre les classes si nécessaire.
     *
     * @param int $rpiId            
     * @return \Zend\Db\Adapter\Driver\ResultInterface|multitype:
     */
    public function getEtablissements($rpiId)
    {
        $t = $this->db_manager->getCanonicName($this->table_name, $this->table_type);
        $select = clone $this->obj_select;
        $select->columns([
            'etablissementId'
        ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], "$t.etablissementId = eta.etablissementId", 
            [
                'nom' => 'nom'
            ])
            ->join(
            [
                'com' => $this->db_manager->getCanonicName('communes', 'table')
            ], "eta.communeId = com.communeId", 
            [
                'commune' => 'nom'
            ])
            ->where($select->where->equalTo('rpiId', $rpiId));
        $statement = $this->table_gateway->getSql()->prepareStatementForSqlObject($select);
        try {
            return $statement->execute();
        } catch (Exception $e) {
            return [];
        }
    }
}