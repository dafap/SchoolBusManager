<?php
/**
 * Test de la classe SbmMailChimp\IndexController
 * 
 * @project sbm
 * @package ModuleTests/SbmMailChimpTest/Controller
 * @filesource IndexControllerTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 sept. 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmMailChimpTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\Permissions\Acl\Acl;
use ModulesTests\Bootstrap;
use SbmMailChimp\Controller\IndexController;
use SbmCommun\Model\Db\Service\DbManager;
use SbmAuthentification\Authentication\AuthenticationServiceFactory;

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
        $this->assertInstanceOf(DbManager::class, $controller->db_manager);
        $this->assertInstanceOf(AuthenticationServiceFactory::class,
            $controller->authenticate);
        $this->assertInstanceOf(Acl::class, $controller->acl);
        $this->assertTrue(is_array($controller->client));
        $this->assertTrue(is_array($controller->mail_config));
        $this->assertTrue(is_string($controller->mailchimp_key));
    }
}