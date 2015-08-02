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
 * @date 1 juil. 2015
 * @version 2015-2
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
         
         $eventManager = $e->getTarget()->getEventManager();
         $eventManager->attach(new PdfListener());
     }
 }