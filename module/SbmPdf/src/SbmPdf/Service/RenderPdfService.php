<?php
/**
 * Cette classe permet de lancer un évènement 'renderPdf' de la manière suivante :
 * 
 * $call_pdf = new RenderPdfService()
 * $call_pdf->setData($rowset)
 *          ->setHead(['column1', 'column2, 'column3')
 *          ->setPdfConfig($setting)
 *          ->setTableConfig($theme)
 *          ->renderPdf();
 *
 *
 * @project sbm
 * @package SbmPdf/Service
 * @filesource RenderPdfService.pdf
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmPdf\Service;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RenderPdfService implements EventManagerAwareInterface, FactoryInterface
{

    /**
     *
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     *
     * @var ServiceManagerInterface
     */
    private $sm;

    /**
     * Tableau structuré pour créer un pdf
     * Les méthodes de construction sont publiques (voir ci-dessous)
     * 
     * @var array
     */
    protected $content = [];

    /**
     * Injecte les objets nécessaires
     *
     * @param ServiceLocatorInterface $serviceLocator            
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::setServiceLocator()
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->sm = $serviceLocator;
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::getServiceLocator()
     */
    public function getServiceLocator()
    {
        return $this->sm;
    }

    /**
     * Lance un évènement renderPdf avec comme `target` le PdfManager et comme `argv` la propriété $content
     */
    public function renderPdf()
    {
        // on donne comme contexte de l'évènement le ServiceManager pour pouvoir l'utiliser plus tard
        $this->getEventManager()->trigger('renderPdf', $this->getServiceLocator(), 
            $this->content);
    }

    /**
     *
     * @param EventManagerInterface $eventManager            
     * @return void
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->addIdentifiers([
            get_called_class()
        ]);
        $this->eventManager = $eventManager;
    }

    /**
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->setEventManager(new EventManager());
        }
        
        return $this->eventManager;
    }

    public function setParam($key, $value)
    {
        $this->content[$key] = $value;
        return $this;
    }

    /**
     * Méthode de construction de la propriété $content
     * Définition de la clé 'data'
     * Nom référencé de la source de données (table, vue, .
     * ..) dans le ServiceManager
     *
     * @param string $data
     *            C'est le nom référencé de la source de données (table, vue, ...)
     * @param array $head            
     *
     * @return \SbmPdf\Service\RenderPdfService
     */
    public function setData($data, $head = [])
    {
        $this->content['data'] = $data;
        if (! empty($head)) {
            $this->setHead($head);
        }
        return $this;
    }

    /**
     * Méthode de construction de la propriété $content
     * Définition de la clé 'head'
     * Noms des colonnes
     *
     * @param array $head
     *            Liste des noms de colonnes
     *            
     * @return \SbmPdf\Service\RenderPdfService
     */
    public function setHead(array $head = [])
    {
        $this->content['head'] = $head;
        return $this;
    }

    /**
     * Méthode de construction de la propriété $content
     * Définition de la clé 'pdf_config'
     * Valeurs propres à ce document
     *
     * @param array $config
     *            Tableau de configuration du pdf qui surcharge les valeurs par défaut
     *            
     * @return \SbmPdf\Service\RenderPdfService
     */
    public function setPdfConfig($config = [])
    {
        $this->content['pdf_config'] = $config;
        return $this;
    }

    /**
     * Méthode de construction de la propriété $content
     * Définition de la clé 'table_config'
     * Description de la mise en forme des données dans le tableau
     *
     * @param array $config            
     * @return \SbmPdf\Service\RenderPdfService
     */
    public function setTableConfig($config = [])
    {
        $this->content['table_config'] = $config;
        return $this;
    }
}