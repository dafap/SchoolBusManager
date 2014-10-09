<?php
/**
 * Description
 *
 *
 * @project sbm
 * @package 
 * @filesource 
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 mai 2014
 * @version 2014-1
 */
namespace DafapTcpdf\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

use DafapTcpdf\Model\Tcpdf as Pdf;

class PdfListener implements ListenerAggregateInterface
{
    /**
     *
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();
    
    /**
     * ServiceManager
     * @var ServiceLocatorInterface
     */
    protected $sm;
    
    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach('DafapTcpdf\Service\RenderPdfService', 'renderPdf', array(
            $this,
            'onRenderPdf'
        ), 100);
    }

    /**
     * (non-PHPdoc)
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
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::getServiceLocator()
     */
    public function getServiceLocator()
    {
        return $this->sm;
    }
    
    public function onRenderPdf($e)
    {
        // le contexte de l'evènement est le ServiceManager
        $pdf = new Pdf($e->getTarget(), $e->getParams());
        $pdf->run();
    }
}


