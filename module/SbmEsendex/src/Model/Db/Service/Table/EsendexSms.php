<?php
/**
 * Gestion de la table `esendexsms`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmEsendex/src/Model/Db/Service/Table
 * @filesource EsendexSms.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model\Db\Service\Table;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;

class EsendexSms extends AbstractSbmTable
{
    protected function init()
    {
        $this->table_name = 'esendexsms';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\EsendexSms';
        $this->id_name = 'esendexsmsId';
    }
}