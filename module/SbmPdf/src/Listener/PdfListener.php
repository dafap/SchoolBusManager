<?php
/**
 * Listener qui traite l'évènement pdfRender lancé par RenderPdfService
 *
 *
 * @project sbm
 * @package SbmPdf/Listener
 * @filesource PdfListener.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmPdf\Listener;

use SbmPdf\Model\Tcpdf;
use SbmPdf\Service\PdfManager;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PdfListener implements ListenerAggregateInterface
{

    /**
     *
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = [];

    /**
     *
     * @var Tcpdf
     */
    protected $pdf_manager;

    public function __construct(ServiceLocatorInterface $pdf_manager)
    {
        if (! ($pdf_manager) instanceof PdfManager) {
            $message = __CLASS__ . ' - PdfManager attendu. On a reçu %s.';
            throw new Exception(sprintf($message, gettype($pdf_manager)));
        }
        $this->pdf_manager = $pdf_manager;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach('SbmPdf\Service\RenderPdfService',
            'renderPdf', [
                $this,
                'onRenderPdf'
            ], 100);
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

    public function onRenderPdf(EventInterface $e)
    {
        $this->pdf_manager->get(Tcpdf::class)
            ->setParams($e->getParams())
            ->run();
    }
}


