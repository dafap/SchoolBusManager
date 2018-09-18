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
 * @date 18 sept. 2018
 * @version 2016-2.4.5
 */
namespace SbmPaiement\Listener;

use SbmBase\Model\StdLib;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Log\Logger;
use Zend\Log\Filter\Priority;
use Zend\Log\Writer\Stream;
use Zend\ServiceManager\ServiceLocatorInterface;

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
     * @var \Zend\ServiceManager\ServiceLocatorInterface
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

    public function __construct(ServiceLocatorInterface $db_manager, $plateforme,
        $config_plateforme)
    {
        if (! ($db_manager) instanceof DbManager) {
            $message = __CLASS__ . ' - DbManager attendu. On a reçu %s.';
            throw new Exception(sprintf($message, gettype($db_manager)));
        }
        $this->db_manager = $db_manager;
        $this->plateforme = $plateforme;
        $this->config_plateforme = $config_plateforme;
        $this->log_file = StdLib::concatPath(
            realpath(StdLib::findParentPath(__DIR__ , 'data/logs')), $plateforme . '_error.log');
    }

    /**
     * Initialise le logger si nécessaire
     */
    private function initLogger()
    {
        if (empty($this->logger)) {
            $filter = new Priority(
                StdLib::getParam('error_reporting', $this->config_plateforme, Logger::WARN));
            $writer = new Stream($this->log_file);
            $writer->addFilter($filter);
            $this->logger = new Logger();
            $this->logger->addWriter($writer);
        }
    }

    /**
     * Configure le logger avant de s'en servir
     *
     * @param int $priority
     * @param mixed $message
     * @param array|\Traversable $extra
     *
     * @return Logger
     *
     * @throws \Zend\Log\Exception\InvalidArgumentException if message can't be cast to string
     * @throws \Zend\Log\Exception\InvalidArgumentException if extra can't be iterated over
     * @throws \Zend\Log\Exception\RuntimeException if no log writer specified
     */
    protected function log($priority, $message, $extra = [])
    {
        $this->initLogger();
        return $this->logger->log($priority, $message, $extra);
    }
}