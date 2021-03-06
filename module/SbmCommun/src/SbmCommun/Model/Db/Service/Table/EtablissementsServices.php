<?php
/**
 * Gestion de la table `etablissements-services`
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource EtablissementsServices.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 mars 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Table;

class EtablissementsServices extends AbstractSbmTable
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'etablissements-services';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\EtablissementsServices';
        $this->id_name = array(
            'etablissementId',
            'serviceId'
        );
    }
}

 