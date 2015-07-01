<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
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