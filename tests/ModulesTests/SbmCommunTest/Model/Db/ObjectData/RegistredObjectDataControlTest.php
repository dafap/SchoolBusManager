<?php
/**
 * Teste l'enregistrement de tous les ObjectData du projet
 *
 * Vérifie si l'objectData est référencé dans db_manager
 * 
 * @project sbm
 * @package ModulesTests/SbmCommunTest/Model/Db/ObjectData
 * @filesource RegistredObjectDataControlTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\Db\ObjectData;

use PHPUnit_Framework_TestCase;
use ModulesTests\ControlListRegisteredClasses;

class RegistredObjectDataControlTest extends PHPUnit_Framework_TestCase
{

    private $ctrl_list;

    public function setUp()
    {
        $this->ctrl_list = new ControlListRegisteredClasses();
        $this->ctrl_list->setSkip(
            [
                'AbstractObjectData',
                'Criteres',
                'Exception',
                'ObjectDataInterface'
            ]);
    }

    public function testDebug()
    {
        $ns = 'SbmCommun\Model\Db\ObjectData';
        $unregistred = $this->ctrl_list->unregistredNamespaceInSection($ns, 'db_manager', 
            'invokables');
        
        $this->assertEmpty($unregistred, implode("\n", $unregistred));
    }
}