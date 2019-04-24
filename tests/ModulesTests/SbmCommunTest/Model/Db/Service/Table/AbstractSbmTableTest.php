<?php
/**
 * Test de création d'un service AbstractSbmTable
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package package_name
 * @filesource AbstractSbmTableTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 juil. 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\Db\Service\Table;

use PHPUnit_Framework_TestCase;
use SbmCommun\Model\Db\Exception as DbException;
use SbmCommun\Model\Db\Service\Table\Exception;
use ModulesTests\Bootstrap;
use ModulesTests\SbmCommunTest\Model\TestAsset;

class AbstractSbmTableTest extends PHPUnit_Framework_TestCase
{

    protected $db_manager;

    public function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $this->db_manager = $serviceManager->get('Sbm\DbManager');
        // enregistre le ObjectData pour le test
        try {
            $this->db_manager->get('Sbm\Db\ObjectData\Test');
        } catch (\Exception $e) {
            $this->db_manager->setService('Sbm\Db\ObjectData\Test',
                new TestAsset\ObjectSbmObjectData());
        }
        // enregistre le TableGateway pour le test
        try {
            $this->db_manager->get('Sbm\Db\TableGateway\Test');
        } catch (\Exception $e) {
            $table_gateway = new TestAsset\TestSbmTableGateway();
            $service = $table_gateway->createService($this->db_manager);
            $this->db_manager->setService('Sbm\Db\TableGateway\Test', $service);
        }
    }

    public function testCreateServiceWithBadDbManager()
    {
        $test_sbm_table = new TestAsset\TestSbmTable();
        $serviceLocator = $this->createMock(
            'Zend\\ServiceManager\\ServiceLocatorInterface');
        $result = false;
        try {
            $result = $test_sbm_table->createService($serviceLocator);
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'Exception d\'un mauvais type.');
        }
        $this->assertFalse($result,
            'La creation du service aurait du provoquer une exception.');
    }

    public function testCreateServiceWithGoodDbManager()
    {
        $this->assertInstanceOf('SbmCommun\Model\Db\Service\DbManager', $this->db_manager,
            'Mauvais type !!!');
        $test_sbm_table = new TestAsset\TestSbmTable();
        // Le service renvoie un TestSbmTable
        try {
            $service = $test_sbm_table->createService($this->db_manager);
            $message = sprintf('%s attendu ; %s reçu.', TestAsset\TestSbmTable::class,
                get_class($service));
            $this->assertInstanceOf(TestAsset\TestSbmTable::class, $service, $message);
        } catch (DbException $e) {
            // la table n'existe pas
            $this->assertEquals(3778, $e->getCode(), $e->getMessage());
        }
    }
}
 