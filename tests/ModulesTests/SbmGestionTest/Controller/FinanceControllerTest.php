<?php
/**
 * Test de la classe SbmGestion\FinanceController
 * 
 * @project sbm
 * @package ModuleTests/SbmGestionTest/Controller
 * @filesource FinanceControllerTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 sept. 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmGestionTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\ServiceManager\ServiceManager;
use ModulesTests\Bootstrap;
use SbmGestion\Controller\FinanceController;
use SbmCommun\Model\Db\Service\DbManager;
use SbmAuthentification\Authentication\AuthenticationServiceFactory;
use SbmPdf\Service\RenderPdfService;

class FinanceControllerTest extends AbstractHttpControllerTestCase
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

    public function testFinanceControllerFactory()
    {
        $controller_manager = $this->serviceManager->get('ControllerManager');
        $controller = $controller_manager->get(FinanceController::class);
        $this->assertInstanceOf(RenderPdfService::class, $controller->RenderPdfService);
        $this->assertInstanceOf(DbManager::class, $controller->db_manager);
        $this->assertInstanceOf(ServiceManager::class, $controller->form_manager);
        $this->assertInstanceOf(ServiceManager::class, $controller->cartographie_manager);
        $this->assertInstanceOf(AuthenticationServiceFactory::class, 
            $controller->authenticate);
        $this->assertTrue(is_array($controller->mail_config));
        $this->assertTrue(is_array($controller->paginator_count_per_page));
    }
}