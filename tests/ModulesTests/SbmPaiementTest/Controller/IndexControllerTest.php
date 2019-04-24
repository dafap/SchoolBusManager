<?php
/**
 * Test de la classe SbmPaiement\IndexController
 * 
 * @project sbm
 * @package ModuleTests/SbmPaiementTest/Controller
 * @filesource IndexControllerTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 sept. 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmPaiementTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\EventManager\EventManagerAwareInterface;
use ModulesTests\Bootstrap;
use SbmPaiement\Controller\IndexController;
use SbmCommun\Model\Db\Service\DbManager;
use SbmAuthentification\Authentication\AuthenticationServiceFactory;
use SbmPdf\Service\RenderPdfService;
use SbmPaiement\Plugin\PlateformeInterface;
use SbmFront\Model\Responsable\Service\ResponsableManager;

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

    public function testIndexControllerFactory()
    {
        $controller_manager = $this->serviceManager->get('ControllerManager');
        $controller = $controller_manager->get(IndexController::class);
        $this->assertInstanceOf(RenderPdfService::class, $controller->RenderPdfService);
        $this->assertInstanceOf(DbManager::class, $controller->db_manager);
        $this->assertInstanceOf(ServiceManager::class, $controller->form_manager);
        $this->assertInstanceOf(PlateformeInterface::class, $controller->plugin_plateforme);
        $this->assertInstanceOf(FactoryInterface::class, $controller->plugin_plateforme);
        $this->assertInstanceOf(EventManagerAwareInterface::class,
            $controller->plugin_plateforme);
        $this->assertInstanceOf(ResponsableManager::class, $controller->responsable);
        $this->assertTrue(is_array($controller->paginator_count_per_page));
        // null ssi user anonyme
        $this->assertTrue(is_array($controller->user) || is_null($controller->user));
        $this->assertTrue(is_array($controller->mail_config));
    }
}