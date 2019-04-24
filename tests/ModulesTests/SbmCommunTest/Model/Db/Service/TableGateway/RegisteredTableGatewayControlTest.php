<?php
/**
 * Teste l'enregistrement de tous les TableGateway du projet
 *
 * 
 * @project sbm
 * @package ModulesTests/SbmCommunTest/Model/Db/Service/TableGateway
 * @filesource RegisteredTableGatewayControlTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\Db\Service\TableGateway;

use PHPUnit_Framework_TestCase;
use ModulesTests\ControlListRegisteredClasses;

class RegisteredTableGatewayControlTest extends PHPUnit_Framework_TestCase
{

    private $ctrl_list;

    public function setUp()
    {
        $this->ctrl_list = new ControlListRegisteredClasses();
        $this->ctrl_list->setSkip([
            'AbstractSbmTableGateway',
            'Exception'
        ]);
    }

    public function testDebug()
    {
        $ns = 'SbmCommun\Model\Db\Service\TableGateway';
        $unregistred = $this->ctrl_list->unregistredNamespaceInSection($ns, 'db_manager',
            'factories');

        $this->assertEmpty($unregistred, implode("\n", $unregistred));
    }
}