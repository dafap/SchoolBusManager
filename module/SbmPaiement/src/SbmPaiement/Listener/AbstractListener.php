<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource AbstractListener.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2015
 * @version 2015-1
 */
namespace SbmPaiement\Listener;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Log\Writer\Stream;
use Zend\Log\Filter\Priority;
use Zend\Log\Logger;
use SbmCommun\Model\StdLib;

abstract class AbstractListener
{
    private $log_file;

    /**
     * Log pour tracer les erreurs
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Service manager
     *
     * @var Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $sm;

    /**
     * Configuration du module
     *
     * @var array
     */
    protected $config;

    /**
     * Installe le service manager
     *
     * @param ServiceLocatorInterface $sm            
     */
    protected function setServiceLocator(ServiceLocatorInterface $sm)
    {
        $this->sm = $sm;
    }

    /**
     * Renvoie le service manager
     *
     * @return Zend\ServiceManager\ServiceLocatorInterface
     */
    protected function getServiceLocator()
    {
        return $this->sm;
    }

    /**
     * Lecture de la configuration
     */
    private function initConfig()
    {
        if (empty($this->config)) {
            $config = $this->getServiceLocator()->get('Config');
            $plateforme = strtolower(StdLib::getParamR(array(
                'sbm',
                'paiement',
                'plateforme'
            ), $config));
            $this->config = StdLib::getParamR(array(
                'sbm',
                'paiement',
                $plateforme
            ), $config);
            $this->log_file = realpath(__DIR__ . '/../../../../../data/logs') . DIRECTORY_SEPARATOR . $plateforme . '_error.log';
        }
    }
    
    /**
     * Initialise le logger si nécessaire
     */
    private function initLogger()
    {
        if (empty($this->logger)) {
            $this->initConfig();
            $filter = new Priority(StdLib::getParam('error_reporting', $this->config, Logger::WARN));
            $writer = new Stream($this->log_file);
            $writer->addFilter($filter);
            $this->logger = new Logger();
            $this->logger->addWriter($writer);
        }
    }
    
    /**
     * Configure le logger avant de s'en servir
     * 
     * @param  int $priority
     * @param  mixed $message
     * @param  array|Traversable $extra
     * @return Logger
     * @throws Exception\InvalidArgumentException if message can't be cast to string
     * @throws Exception\InvalidArgumentException if extra can't be iterated over
     * @throws Exception\RuntimeException if no log writer specified
     */
    protected function log($priority, $message, $extra = array())
    {
        $this->initLogger();
        return $this->logger->log($priority, $message, $extra);
    }
}