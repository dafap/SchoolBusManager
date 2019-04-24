<?php
/**
 * Test de la classe SbmPdf\DocumentController
 * 
 * @project sbm
 * @package ModuleTests/SbmPdfTest/Controller
 * @filesource DocumentControllerTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 sept. 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmPdfTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\ServiceManager\ServiceManager;
use ModulesTests\Bootstrap;
use SbmPdf\Controller\DocumentController;
use SbmCommun\Model\Db\Service\DbManager;
use SbmAuthentification\Authentication\AuthenticationServiceFactory;
use SbmFront\Model\Responsable\Service\ResponsableManager;

class DocumentControllerTest extends AbstractHttpControllerTestCase
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

    public function testDocumentControllerFactory()
    {
        $controller_manager = $this->serviceManager->get('ControllerManager');
        $controller = $controller_manager->get(DocumentController::class);
        $this->assertInstanceOf(DbManager::class, $controller->db_manager);
        $this->assertInstanceOf(ServiceManager::class, $controller->pdf_manager);
        $this->assertInstanceOf(AuthenticationServiceFactory::class,
            $controller->authenticate);
        $this->assertInstanceOf(ResponsableManager::class, $controller->responsable);
    }
}