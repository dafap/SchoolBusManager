<?php
/**
 * Gestion de la table `rpi-communes`
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Table
 * @filesource RpiCommunes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 août 2018
 * @version 2018-2.4.2
 */
namespace SbmCommun\Model\Db\Service\Table;

class RpiCommunes extends AbstractRpiTable
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'rpi-communes';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\RpiCommunes';
        $this->id_name = [
            'rpiId',
            'communeId'
        ];
    }

    /**
     * Donne l'identifiant du rpi de la commune donnée en paramètre ou lance une exception
     * si la commune n'est pas dans la table `rpi-communes`.
     *
     * @param string $communeId            
     *
     * @return int
     *
     * @throws \SbmCommun\Model\Db\Service\Table\Exception
     */
    public function getRpiId($communeId)
    {
        $resultset = $this->fetchAll(
            [
                'communeId' => $communeId
            ]);
        if ($resultset->count()) {
            return $resultset->current()->rpiId;
        } else {
            throw new Exception(
                sprintf('La commune %s n\'est pas dans la table `rpi-communes`.', 
                    $communeId));
        }
    }

    /**
     * Renvoie un tableau de communeId correspondant aux communes du même RPI que le
     * paramètre indiqué.
     * Si cette commune n'est pas en RPI, renvoie un tableau composé
     * de cette $communeId.
     *
     * @param string $communeId            
     * @return array
     */
    public function getCommuneIds($communeId)
    {
        try {
            $rpiId = $this->getRpiId($communeId);
            $result = [];
            $resultset = $this->fetchAll(
                [
                    'rpiId' => $rpiId
                ]);
            foreach ($resultset as $row) {
                $result[] = $row->communeId;
            }
            if (empty($result)) {
                $result = (array) $communeId;
            }
            return $result;
        } catch (Exception $e) {
            return (array) $communeId;
        }
    }

    /**
     * Renvoie un tableau
     *
     * @param int $rpiId            
     * @return array|multitype:
     */
    public function getCommunes($rpiId)
    {
        $t = $this->db_manager->getCanonicName($this->table_name, $this->table_type);
        $select = clone $this->obj_select;
        $select->columns([
            'communeId'
        ])
            ->join(
            [
                'com' => $this->db_manager->getCanonicName('communes', 'table')
            ], "$t.communeId = com.communeId", 
            [
                'nom' => 'nom'
            ])
            ->where($select->where->equalTo('rpiId', $rpiId));
        $statement = $this->table_gateway->getSql()->prepareStatementForSqlObject($select);
        try {
            return iterator_to_array($statement->execute());
        } catch (Exception $e) {
            return [];
        }
    }
}