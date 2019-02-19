<?php
/**
 * Gestion de la table `transporteurs`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Transporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table;

use Zend\Db\Sql\Where;

class Transporteurs extends AbstractSbmTable
{

    /**
     * Initialisation du transporteur
     */
    protected function init()
    {
        $this->table_name = 'transporteurs';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Transporteurs';
        $this->id_name = 'transporteurId';
    }

    public function setSelection($transporteurId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(
            [
                'transporteurId' => $transporteurId,
                'selection' => $selection
            ]);
        parent::saveRecord($oData);
    }

    /**
     *
     * @param string $email
     *
     * @throws \SbmCommun\Model\Db\Service\Table\Exception\RuntimeException
     *
     * @return int
     */
    public function getTransporteurId($email)
    {
        $where = new Where();
        $where->equalTo('email', $email);
        $result = $this->fetchAll($where);
        if ($result->count() == 1) {
            return $result->current()->transporteurId;
        } else {
            throw new Exception\RuntimeException('Impossible de trouver ce transporteur');
        }
    }
}

