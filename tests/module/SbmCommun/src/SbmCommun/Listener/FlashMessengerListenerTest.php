<?php
/**
 * Test pour le listener FlashMessengerListener
 *
 *
 * @project sbm
 * @package tests/SbmCommun/.../Listener
 * @filesource FlashMessengerListenerTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 avr. 2014
 * @version 2014-1
 */
namespace SbmCommunTest\Listener;

use PHPUnit_Framework_TestCase;
use Zend\EventManager\EventManager;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\View\Model\ViewModel;
use Zend\Mvc\ApplicationInterface;
use Zend\Mvc\Controller\AbstractActionController;
use SbmCommun\Listener\FlashMessengerListener;

class FlashMessengerListenerTest extends PHPUnit_Framework_TestCase
{
    const MESSAGE_TEST = "Ex sexus quo fuga conductae ut est ad in sexus.";
    
    protected $listener;

    public function setUp()
    {
        $this->listener = new FlashMessengerListener();
    }

    /**
     * Vérifie si le listener est monté avec la bonne méthode callback et la bonne priority
     */
    public function testAttachesRendererAtExpectedPriority()
    {
        $event = new EventManager();
        $event->attachAggregate($this->listener);
        $listeners = $event->getListeners(MvcEvent::EVENT_DISPATCH);
        
        $expectedCallback = array(
            $this->listener,
            'onDispatch'
        );
        $expectedPriority = - 9500;
        
        $found = false;
        foreach ($listeners as $listener) {
            $callback = $listener->getCallback();
            if ($callback == $expectedCallback) {
                if ($listener->getMetadatum('priority') == $expectedPriority) {
                    $found = true;
                    break;
                }
            }
        }
        $this->assertTrue($found, 'FlashMessengerListener mal monté.');
    }

    /**
     * Vérifie qu'on peut détacher le listener
     */
    public function testCanDetachListenersFromEventManager()
    {
        $events = new EventManager();
        $events->attachAggregate($this->listener);
        $this->assertEquals(1, count($events->getListeners(MvcEvent::EVENT_DISPATCH)));
        
        $events->detachAggregate($this->listener);
        $this->assertEquals(0, count($events->getListeners(MvcEvent::EVENT_DISPATCH)));
    }

    /**
     * Vérifie que la propriété FlashMessages est affectée après un addSuccessMessage()
     * Attention ! ici le flashMessenger ne fonctionne pas bien car il ne rend rien par getSuccessMessages()
     * Je teste seulement que getCurrentSuccessMessages() rend bien la valeur donnée.
     */
    public function testOnDispatchIssetFlashMessages()
    {
        $target = new IndexController();
        
        $target->preTest();
        ob_start();
        var_dump($target->flashMessenger()->getCurrentSuccessMessages());
        $err_message = ob_get_clean();
        $this->assertEquals(array(self::MESSAGE_TEST), $target->flashMessenger()->getCurrentSuccessMessages(), $err_message);
        $this->assertFalse($target->flashMessageIsSet(), 'Le flashMessages a deja une valeur dans le layout.');
        
        $e = new MvcEvent();
        $e->setTarget($target);
        $this->listener->onDispatch($e);
                        
        $this->assertTrue($target->flashMessageIsSet(), 'Le flashMessages n\'est pas affecté dans le layout.');                       
    }
}


/**
 * Définition d'un controller pour ces tests
 * 
 * @author admin
 *        
 */
class IndexController extends AbstractActionController
{

    public function flashMessageIsSet()
    {
        return isset($this->layout()->flashMessages);
    }
    
    public function preTest()
    {
        $this->flashMessenger()->addSuccessMessage(FlashMessengerListenerTest::MESSAGE_TEST);
    }
}