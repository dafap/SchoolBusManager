<?php
/**
 * Test de la classe SbmGestion\ConfigController
 * 
 * @project sbm
 * @package ModuleTests/SbmGestionTest/Controller
 * @filesource ConfigControllerTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 sept. 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmGestionTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\Http\Response;
use ModulesTests\Bootstrap;
use SbmGestion\Controller\ConfigController;

class ConfigControllerTest extends AbstractHttpControllerTestCase
{

    private $serviceManager;

    private $controller;

    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            Bootstrap::getServiceManager()->get('ApplicationConfig'));
        parent::setUp();
        $this->serviceManager = $this->getApplicationServiceLocator();
        $controller_manager = $this->serviceManager->get('ControllerManager');
        $this->controller = $controller_manager->get(ConfigController::class);
    }

    public function testEmailChangeActionResponse()
    {
        // $this->assertInstanceOf(Response::class,
        // $this->controller->emailChangeAction());
    }

    public function testIndexChangeActionResponse()
    {
        // $this->assertInstanceOf(Response::class,
        // $this->controller->indexChangeAction());
    }

    public function testLocalisationActionResponse()
    {
        // $this->assertInstanceOf(Response::class,
        // $this->controller->localisationAction());
    }

    public function testMdpChangeActionResponse()
    {
        // $this->assertInstanceOf(Response::class, $this->controller->mdpChangeAction());
    }

    public function testMessageActionResponse()
    {
        // $this->assertInstanceOf(Response::class, $this->controller->messageAction());
    }

    public function testModifCompteActionResponse()
    {
        // $this->assertInstanceOf(Response::class,
        // $this->controller->modifCompteAction());
    }
}