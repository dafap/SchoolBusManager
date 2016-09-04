<?php
/**
 * Test de la classe SbmPdf\PdfController
 * 
 * @project sbm
 * @package ModuleTests/SbmPdfTest/Controller
 * @filesource PdfControllerTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 sept. 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmPdfTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\ServiceManager\ServiceManager;
use ModulesTests\Bootstrap;
use SbmPdf\Controller\PdfController;
use SbmCommun\Model\Db\Service\DbManager;
use SbmPdf\Service\RenderPdfService;

class PdfControllerTest extends AbstractHttpControllerTestCase
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
    
    public function testPdfControllerFactory()
    {
        $controller_manager = $this->serviceManager->get('ControllerManager');
        $controller = $controller_manager->get(PdfController::class);
        $this->assertInstanceOf(DbManager::class, $controller->db_manager);
        $this->assertInstanceOf(ServiceManager::class, $controller->pdf_manager);
        $this->assertInstanceOf(RenderPdfService::class, $controller->RenderPdfService);
    }
}