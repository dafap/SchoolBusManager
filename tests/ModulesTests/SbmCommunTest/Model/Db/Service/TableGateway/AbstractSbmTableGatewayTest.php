<?php
/**
 * Test de création d'un service AbstractSbmTableGateway
 *
 * @project sbm
 * @package ModulesTests/SbmCommunTest/Model/Db/Service/TableGateway
 * @filesource AbstractSbmTableGatewayTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\Db\Service\TableGateway;

use PHPUnit_Framework_TestCase;
use SbmCommun\Model\Db\Service\TableGateway\Exception;
use ModulesTests\ServiceManagerGrabber;
use ModulesTests\SbmCommunTest\Model\TestAsset;
use Zend\Db\TableGateway\TableGateway;

class AbstractSbmTableGatewayTest extends PHPUnit_Framework_TestCase
{

    protected $serviceManager;

    public function setUp()
    {
        $serviceManagerGrabber = new ServiceManagerGrabber();
        $this->serviceManager = $serviceManagerGrabber->getServiceManager();
    }

    public function testCreateServiceWithBadDbManager()
    {
        $test_sbm_table = new TestAsset\TestSbmTableGateway();
        $serviceLocator = $this->createMock('Zend\\ServiceManager\\ServiceLocatorInterface');
        $result = false;
        try {
            $result = $test_sbm_table->createService($serviceLocator);
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'Exception d\'un mauvais type.');
        }
        $this->assertFalse($result, 'La creation du service aurait du provoquer une exception.');
    }

    public function testCreateServiceWithGoodDbManager()
    {
        $db_manager = $this->serviceManager->get('Sbm\DbManager');
        $this->assertInstanceOf('SbmCommun\Model\Db\Service\DbManager', $db_manager, 'Mauvais type !!!');
        $db_manager->setService('Sbm\Db\ObjectData\Test', new TestAsset\ObjectSbmObjectData());
        $test_sbm_table = new TestAsset\TestSbmTableGateway();
        // Le service renvoie un TableGateway
        $service = $test_sbm_table->createService($db_manager);
        $message = sprintf('%s attendu ; %s reçu.', TableGateway::class, get_class($service));
        $this->assertInstanceOf(TableGateway::class, $service, $message);
        // Teste le type de l'object_data associé
        $prototype = $service->getResultSetPrototype();
        $object_data = $prototype->getObjectPrototype();
        $this->assertInstanceOf(TestAsset\ObjectSbmObjectData::class, $object_data, 'Classe non trouvee pour ObjectData !!!');
    }
}