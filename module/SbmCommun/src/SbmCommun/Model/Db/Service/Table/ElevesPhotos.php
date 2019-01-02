<?php
/**
 * Gestion de la table `elevesphotos`
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource ElevesPhotos.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 déc. 2018
 * @version 2018-2.4.6
 */
namespace SbmCommun\Model\Db\Service\Table;

class ElevesPhotos extends AbstractSbmTable
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'elevesphotos';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\ElevesPhotos';
        $this->id_name = 'eleveId';
    }
}