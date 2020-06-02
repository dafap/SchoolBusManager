<?php
/**
 * Gestion de la table `esendextelephones`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmEsendex/src/Model/Db/Service/Table
 * @filesource EsendexTelephones.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model\Db\Service\Table;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;

class EsendexTelephones extends AbstractSbmTable
{
    protected function init()
    {
        $this->table_name = 'esendextelephones';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\EsendexTelephones';
        $this->id_name = 'esendextelephoneId';
    }
}