<?php
/**
 * Gestion de la table `cleversms`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCleverSms/src/Model/Db/Service/Table
 * @filesource CleverSms.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCleverSms\Model\Db\Service\Table;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;

class CleverSms extends AbstractSbmTable
{
    protected function init()
    {
        $this->table_name = 'cleversms';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\CleverSms';
        $this->id_name = 'cleversmsId';
    }
}