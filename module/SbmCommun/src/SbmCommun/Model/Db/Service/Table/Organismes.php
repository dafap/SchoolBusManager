<?php
/**
 * Gestion de la table `organismes`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Organismes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 oct. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Table;

use Zend\Db\Sql\Where;

class Organismes extends AbstractSbmTable
{
    /**
     * Initialisation de l'organisme
     */
    protected function init()
    {
        $this->table_name = 'organismes';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Organismes';
        $this->id_name = 'organismeId';
    }
    
    public function setSelection($organismeId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(array(
            'organismeId' => $organismeId,
            'selection' => $selection
        ));
        parent::saveRecord($oData);
    }
    
    public function getOrganismeId($email)
    {
        $where = new Where();
        $where->equalTo('email', $email);
        $result = $this->fetchAll($where);
        if ($result->count() == 1) {
            return $result->current()->organismeId;
        } else {
            throw new \Exception('Impossible de trouver cet organisme');
        }
    }
}

