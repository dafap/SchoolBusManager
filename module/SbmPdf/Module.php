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
 * @date 13 avr. 2016
 * @version 2016-2
 */
 namespace SbmPdf;
 
 use ZfcBase\Module\AbstractModule;
 use Zend\Mvc\MvcEvent;
 use SbmPdf\Listener\PdfListener;
 
 
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
         $pdfListener = $e->getApplication()->getServiceManager()->get(PdfListener::class);
         $eventManager = $e->getTarget()->getEventManager();         
         $eventManager->attach($pdfListener);
     }
 }