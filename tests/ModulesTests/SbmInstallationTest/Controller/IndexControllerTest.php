<?php
/**
 * Test de la classe SbmInstallation\IndexController
 * 
 * @project sbm
 * @package ModuleTests/SbmInstallation/Controller
 * @filesource IndexControllerTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 sept. 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmInstallationTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\ServiceManager\ServiceManager;
use ModulesTests\Bootstrap;
use SbmInstallation\Controller\IndexController;
use SbmCommun\Model\Db\Service\DbManager;
use SbmAuthentification\Authentication\AuthenticationServiceFactory;
use SbmPdf\Service\RenderPdfService;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    private $serviceManager;
    protected $traceError = true;
    
    public function setUp()
    {
        $this->setApplicationConfig(
            Bootstrap::getServiceManager()->get('ApplicationConfig')
        );
        parent::setUp();
        $this->serviceManager = $this->getApplicationServiceLocator();
    }
    
    public function testIndexControllerFactory()
    {
        $controller_manager = $this->serviceManager->get('ControllerManager');
        $controller = $controller_manager->get(IndexController::class);
        $this->assertInstanceOf(DbManager::class, $controller->db_manager);
        $this->assertTrue(is_array($controller->db_config));
        $this->assertTrue(is_array($controller->config_paiement));
        $this->assertTrue(is_string($controller->error_log));
        $this->assertTrue(is_array($controller->mailchimp_key));
        $this->assertTrue(is_array($controller->img));
    }
}