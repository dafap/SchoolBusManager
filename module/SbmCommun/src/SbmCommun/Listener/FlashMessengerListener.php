<?php
/**
 * Listener pour écouter les messages de FlashMessenger
 *
 *
 * @project sbm
 * @package SbmCommun/Listener
 * @filesource FlashMessengerListener
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Listener;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\Plugin\FlashMessenger;

class FlashMessengerListener implements ListenerAggregateInterface
{

    /**
     *
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();
    /*
     * (non-PHPdoc) @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array(
            $this,
            'onDispatch'
        ), - 9500);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\EventManager\ListenerAggregateInterface::detach()
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * MvcEvent::EVENT_DISPATCH event callback
     *
     * @param MvcEvent $event            
     */
    public function onDispatch(MvcEvent $event)
    {
        $controller = $event->getTarget();
        if (! $controller) {
            $controller = $event->getRouteMatch()->getParam('controller', '');
        }
        $messages = array();
        if ($controller->flashMessenger()->hasSuccessMessages()) {
            $messages['success'] = $controller->flashMessenger()->getSuccessMessages();
        }
        if ($controller->flashMessenger()->hasWarningMessages()) {
            $messages['warning'] = $controller->flashMessenger()->getWarningMessages();
        }
        if ($controller->flashMessenger()->hasErrorMessages()) {
            $messages['danger'] = $controller->flashMessenger()->getErrorMessages();
        }
        if ($controller->flashMessenger()->hasInfoMessages()) {
            $messages['info'] = $controller->flashMessenger()->getInfoMessages();
        }
        $controller->layout()->flashMessages = $messages;
    }
}