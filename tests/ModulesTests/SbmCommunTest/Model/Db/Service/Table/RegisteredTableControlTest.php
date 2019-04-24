<?php
/**
 * Teste l'enregistrement de tous les Table du projet
 * 
 * @project sbm
 * @package ModulesTests/SbmCommunTest/Model/Db/Service/Table
 * @filesource RegisteredTableControlTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\Db\Service\Table;

use PHPUnit_Framework_TestCase;
use ModulesTests\ControlListRegisteredClasses;

class RegisteredTableControlTest extends PHPUnit_Framework_TestCase
{

    private $ctrl_list;

    public function setUp()
    {
        $this->ctrl_list = new ControlListRegisteredClasses();
        $this->ctrl_list->setSkip([
            'AbstractSbmTable',
            'Exception'
        ]);
    }

    public function testDebug()
    {
        $ns = 'SbmCommun\Model\Db\Service\Table';
        $unregistred = $this->ctrl_list->unregistredNamespaceInSection($ns, 'db_manager',
            'factories');

        $this->assertEmpty($unregistred, implode("\n", $unregistred));
    }
}