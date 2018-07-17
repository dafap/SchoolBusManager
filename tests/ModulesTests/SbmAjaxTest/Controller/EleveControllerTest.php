<?php
/**
 * Test de la classe SbmAjax\EleveController
 * 
 * @project sbm
 * @package ModulesTests/SbmAjaxTest/Controller
 * @filesource EleveControllerTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 sept. 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmAjaxTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Console\Console;
use Zend\ServiceManager\ServiceManager;
use ModulesTests\Bootstrap;
use SbmAjax\Controller\EleveController;
use SbmCommun\Model\Db\Service\DbManager;

class EleveControllerTest extends AbstractHttpControllerTestCase
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

    public function testEleveControllerFactory()
    {
        $controller_manager = $this->serviceManager->get('ControllerManager');
        $controller = $controller_manager->get(EleveController::ROUTE);
        $this->assertInstanceOf(DbManager::class, $controller->db_manager);
        $this->assertInstanceOf(ServiceManager::class, $controller->form_manager);
        $this->assertInstanceOf(ServiceManager::class, $controller->cartographie_manager);
    }
}