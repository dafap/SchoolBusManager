<?php
/**
 * Test de la classe SbmParent\ConfigController
 * 
 * @project sbm
 * @package ModuleTests/SbmParentTest/Controller
 * @filesource ConfigControllerTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 sept. 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmParentTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\ServiceManager\ServiceManager;
use ModulesTests\Bootstrap;
use SbmParent\Controller\ConfigController;
use SbmCommun\Model\Db\Service\DbManager;
use SbmAuthentification\Authentication\AuthenticationServiceFactory;
use SbmFront\Model\Responsable\Service\ResponsableManager;
use SbmPdf\Service\RenderPdfService;

class ConfigControllerTest extends AbstractHttpControllerTestCase
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

    public function testConfigControllerFactory()
    {
        $controller_manager = $this->serviceManager->get('ControllerManager');
        $controller = $controller_manager->get(ConfigController::class);
        $this->assertInstanceOf(DbManager::class, $controller->db_manager);
        $this->assertInstanceOf(ServiceManager::class, $controller->form_manager);
        $this->assertInstanceOf(ServiceManager::class, $controller->cartographie_manager);
        $this->assertInstanceOf(AuthenticationServiceFactory::class, 
            $controller->authenticate);
        $this->assertInstanceOf(ResponsableManager::class, $controller->responsable);
        $this->assertTrue(is_array($controller->client));
        $this->assertTrue(is_string($controller->accueil));
        $this->assertTrue(is_array($controller->paginator_count_per_page));
    }
}