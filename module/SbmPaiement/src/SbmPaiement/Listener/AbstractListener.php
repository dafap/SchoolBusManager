<?php
/**
 * Base commune aux listeners PaiementOK et ScolariteOK
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmPaiement/Listener
 * @filesource AbstractListener.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 avr. 2016
 * @version 2016-2
 */
namespace SbmPaiement\Listener;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Log\Writer\Stream;
use Zend\Log\Filter\Priority;
use Zend\Log\Logger;
use SbmCommun\Model\StdLib;
use SbmCommun\Model\Db\Service\DbManager;

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
     * Db manager
     *
     * @var Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $db_manager;
    
    /**
     * Nom de la plateforme
     * 
     * @var string
     */
    protected $plateforme;

    /**
     * Configuration de la plateforme
     *
     * @var array
     */
    protected $config_plateforme;
        

    public function __construct(ServiceLocatorInterface $db_manager, $plateforme, $config_plateforme)
    {
        if (! ($db_manager) instanceof DbManager) {
            $message = __CLASS__ . ' - DbManager attendu. On a reçu %s.';
            throw new Exception(sprintf($message, gettype($db_manager)));
        }
        $this->db_manager = $db_manager;
        $this->plateforme = $plateforme;
        $this->config_plateforme = $config_plateforme;
        $this->log_file = realpath(__DIR__ . '/../../../../../data/logs') . DIRECTORY_SEPARATOR . $plateforme . '_error.log';
    }
    
    /**
     * Initialise le logger si nécessaire
     */
    private function initLogger()
    {
        if (empty($this->logger)) {
            $filter = new Priority(StdLib::getParam('error_reporting', $this->config_plateforme, Logger::WARN));
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