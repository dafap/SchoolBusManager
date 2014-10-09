<?php
/**
 * Gestion de la table `communes`
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Communes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table;

class Communes extends AbstractSbmTable
{
    /**
     * Initialisation de la commune
     */
    protected function init()
    {
        $this->table_name = 'communes';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Communes';
        $this->id_name = 'communeId';
    }
}

