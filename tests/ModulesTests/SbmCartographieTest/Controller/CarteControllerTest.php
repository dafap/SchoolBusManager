<?php
/**
 * Test de la classe SbmCartographie\CarteController
 * 
 * @project sbm
 * @package ModuleTests/SbmCartographieTest/Controller
 * @filesource CarteControllerTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 sept. 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmCartographieTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\ServiceManager\ServiceManager;
use ModulesTests\Bootstrap;
use SbmCartographie\Controller\CarteController;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;

class CarteControllerTest extends AbstractHttpControllerTestCase
{

    private $serviceManager;

    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            Bootstrap::getServiceManager()->get('ApplicationConfig'));
        parent::setUp();
        $this->serviceManager = $this->getApplicationServiceLocator();
    }

    public function testCarteControllerFactory()
    {
        $controller_manager = $this->serviceManager->get('ControllerManager');
        $controller = $controller_manager->get(CarteController::class);
        $this->assertTrue(is_array($controller->config_cartes));
        // null ssi user anonyme
        $this->assertTrue(is_array($controller->user) || is_null($controller->user));
        $this->assertInstanceOf(DbManager::class, $controller->db_manager);
        $this->assertInstanceOf(ProjectionInterface::class, $controller->projection);
    }
}