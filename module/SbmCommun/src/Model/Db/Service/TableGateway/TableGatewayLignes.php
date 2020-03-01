<?php
/**
 * Service donnant un Tablegateway pour le table Lignes
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/TableGateway
 * @filesource TableGatewayLignes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 fév. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

class TableGatewayLignes extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'lignes';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Ligne';
    }
}