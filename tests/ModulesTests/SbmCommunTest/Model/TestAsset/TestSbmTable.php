<?php
/**
 * Objet dérivé de AbstractSbmTable servant à créer un objet à tester
 *
 * @project sbm
 * @package ModulesTests/SbmCommunTest/Model/TestAsset
 * @filesource TestSbmTable.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 juil. 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\TestAsset;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;

class TestSbmTable extends AbstractSbmTable
{

    protected function init()
    {
        $this->table_name = 'test';
        $this->table_type = 'table';
        $this->table_gateway = 'Sbm\Db\TableGateway\Test';
        $this->id_name = 'testId';
    }
}