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
 * @date 9 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Service\Table;

class RpiClasses extends AbstractSbmTable
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
}