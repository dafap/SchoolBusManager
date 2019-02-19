<?php
/**
 * Test de la classe SbmFront\IndexController
 * 
 * @project sbm
 * @package ModulesTests/SbmFrontTest/Controller
 * @filesource IndexControllerTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 sept. 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmFrontTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use ModulesTests\Bootstrap;
use SbmFront\Controller\IndexController;
use SbmFront\Form\Login;
use SbmCommun\Model\Db\Service\DbManager;

class IndexControllerTest extends AbstractHttpControllerTestCase
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

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('SbmFront');
        $this->assertControllerName(IndexController::class);
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('home');
    }

    public function testIndexControllerFactory()
    {
        $controller_manager = $this->serviceManager->get('ControllerManager');
        $controller = $controller_manager->get(IndexController::class);
        $this->assertTrue(is_array($controller->client));
        $this->assertTrue(is_string($controller->accueil));
        $this->assertInstanceOf(Login::class, $controller->login_form);
        $this->assertInstanceOf(DbManager::class, $controller->db_manager);
    }
}