<?php
/**
 * Module de gestion des documents Pdf
 *
 * Mise en place d'un listener pour l'évènement pdfRender
 * Traitement de l'évènement pour créer des documents pdf
 * Gestion d'un ensemble de tables permettant de paramétrer les documents pdf à créer
 * 
 * @project sbm
 * @package SbmPdf
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmPdf;

use SbmBase\Module\AbstractModule;
use SbmPdf\Listener\PdfListener;
use Zend\Mvc\MvcEvent;

class Module extends AbstractModule
{

    public function getDir()
    {
        return __DIR__;
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function onBootstrap(MvcEvent $e)
    {
        $pdfListener = $e->getApplication()
            ->getServiceManager()
            ->get(PdfListener::class);
        $eventManager = $e->getTarget()->getEventManager();
        $eventManager->attach($pdfListener);
    }
}