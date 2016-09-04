<?php
/**
 * Test de la classe SbmAjax\FinanceController
 * 
 * @project sbm
 * @package ModulesTests/SbmAjaxTest/Controller
 * @filesource FinanceControllerTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 sept. 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmAjaxTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Console\Console;
use ModulesTests\Bootstrap;
use SbmAjax\Controller\FinanceController;
use SbmCommun\Model\Db\Service\DbManager;

class FinanceControllerTest extends AbstractHttpControllerTestCase
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
    
    public function testFinanceControllerFactory()
    {
        $controller_manager = $this->serviceManager->get('ControllerManager');
        $controller = $controller_manager->get(FinanceController::ROUTE);
        $this->assertInstanceOf(DbManager::class, $controller->db_manager);
    }
}