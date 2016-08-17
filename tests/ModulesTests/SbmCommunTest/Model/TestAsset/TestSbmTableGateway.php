<?php
/**
 * Objet dérivé de AbstractSbmTableGateway servant à créer un objet à tester
 * 
 * @project sbm
 * @package ModulesTests\SbmCommunTest\Model\TestAsset
 * @filesource TestSbmTableGateway.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\TestAsset;

use SbmCommun\Model\Db\Service\TableGateway\AbstractSbmTableGateway;

class TestSbmTableGateway extends AbstractSbmTableGateway
{
    protected function init()
    {
        $this->table_name = 'test';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Test';
    }
}