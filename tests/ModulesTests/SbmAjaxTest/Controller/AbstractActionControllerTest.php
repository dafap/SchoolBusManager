<?php
/**
 * Test de la classe SbmAjax\AbstractActionController
 * 
 * @project sbm
 * @package ModulesTests/SbmAjaxTest/Controller
 * @filesource AbstractActionControllerTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 août 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmAjaxTest\Controller;

use PHPUnit\Framework\TestCase;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use SbmAjax\Controller\AbstractActionController as SampleController;

class AbstractActionControllerTest extends TestCase
{

    public function setUp()
    {
        $this->request = new Request();
        $event = new MvcEvent();
        $event->setRequest($this->request);
        $event->setResponse(new Response());
        $event->setRouteMatch(new RouteMatch([
            'value' => 'rm:1234',
            'other' => '1234:rm',
            'args' => 'rm:1234/kl:azerty'
        ]));
        $this->controller = new SampleController();
        $this->controller->setEvent($event);
        // $this->plugin = $this->controller->plugin('params');
    }
    
    /**
     * Test du comportement hérité du plugin Params
     * 
     */
    public function testFromRouteIsDefault()
    {
        $value = $this->controller->params('value');
        $this->assertEquals($value, 'rm:1234');
    }
    public function testFromRouteReturnsDefaultIfSet()
    {
        $value = $this->controller->params('foo', 'bar');
        $this->assertEquals($value, 'bar');
    }
    public function testFromRouteReturnsExpectedValue()
    {
        $value = $this->controller->params('value');
        $this->assertEquals($value, 'rm:1234');
    }
    /**
     * Nouveau comportement pour args
     */
    public function testFromRouteArgs()
    {
        $expected = ['rm' => 1234, 'kl' => 'azerty'];
        $args = $this->controller->params('args');
        $this->assertEquals($expected, $args);
    }
    public function testFromRouteRmInArgs()
    {
        $expected = 1234;
        $args = $this->controller->params('rm');
        $this->assertEquals($expected, $args);
    }
    
    /**
     * Test du comportement de __get dans le cas où il lance une exception
     */
    public function testUnvalideGet()
    {
        try {
            $tmp = $this->controller->fantome;
            $this->assertTrue(false, 'Aurait du lancer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(\SbmAjax\Controller\Exception::class, $e, $e->getMessage());
        }
    }
}