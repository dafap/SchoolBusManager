<?php
/**
 * Test de la classe SbmFront\LoginController
 * 
 * @project sbm
 * @package ModulesTests/SbmFrontTest/Controller
 * @filesource LoginControllerTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 sept. 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmfrontTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\ServiceManager\ServiceManager;
use ModulesTests\Bootstrap;
use SbmFront\Controller\LoginController;
use SbmAuthentification\Authentication\AuthenticationServiceFactory;
use SbmFront\Model\Responsable\Service\ResponsableManager;
use SbmCartographie\GoogleMaps\DistanceEtablissements;
use SbmCommun\Model\Db\Service\DbManager;

class LoginControllerTest extends AbstractHttpControllerTestCase
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
        $controller = $controller_manager->get(LoginController::class);
        $this->assertInstanceOf(DbManager::class, $controller->db_manager);
        $this->assertInstanceOf(ServiceManager::class, $controller->form_manager);
        $this->assertInstanceOf(AuthenticationServiceFactory::class, 
            $controller->authenticate);
        $this->assertInstanceOf(ResponsableManager::class, $controller->responsable);
        $this->assertInstanceOf(DistanceEtablissements::class, 
            $controller->distance_etablissements);
        $this->assertTrue(is_array($controller->config_cartes));
        $this->assertTrue(is_array($controller->mail_config));
        $this->assertTrue(is_array($controller->img));
        $this->assertTrue(is_array($controller->client));
    }
    
    /*
     * public function testCheckselectionuserActionCanBeAccessed()
     * {
     * $this->dispatch('/');
     * $this->assertResponseStatusCode(200);
     *
     * $this->assertModuleName('SbmFront');
     * $this->assertControllerName('SbmFront\Controller\IndexController');
     * $this->assertControllerClass('IndexController');
     * $this->assertMatchedRouteName('home');
     * }
     */
}