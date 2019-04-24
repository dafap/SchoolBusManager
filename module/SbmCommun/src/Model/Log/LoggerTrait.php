<?php
/**
 * Gestion d'un Logger pour enregistrer des résultats (erreurs ou historique) dans un fichier structuré
 *
 * Usage 1 :
 * initialiser le logger par la méthode setLogger()
 * utiliser le logger par $this->getLogger()->log()
 *
 * Usage 2 :
 * initialiser le filelog par la méthode setFileLog()
 * utiliser le logger par $this->getLogger()->log()
 * (au premier appel, le logger sera créé)
 *
 * @project sbm
 * @package SbmCommun/src/Model/Log
 * @filesource LoggerTrait.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Log;

use SbmBase\Model\StdLib;
use Zend\Log;

trait LoggerTrait
{

    /**
     *
     * @var string
     */
    protected $filelog;

    /**
     *
     * @var \Zend\Log\LoggerInterface
     */
    protected $logger = null;

    /**
     *
     * @var integer
     */
    protected $priority = Log\Logger::WARN;

    /**
     * Set logger object
     *
     * @param \Zend\Log\LoggerInterface $logger
     * @return mixed
     */
    public function setLogger(Log\LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Get logger object Par défaut, le logger sera créé si la propriété `fileloge` est
     * initialisée.
     *
     * @return \Zend\Log\LoggerInterface
     */
    public function getLogger()
    {
        if (empty($this->logger)) {
            $this->init();
        }
        return $this->logger;
    }

    /**
     * Donner le chemin et le nom du fichier log à créer. Peu importe si le chemin se
     * termine ou non par un séparateur.
     *
     * @param string $path
     * @param string $filename
     */
    public function setFileLog(string $path, string $filename)
    {
        $this->filelog = StdLib::concatPath($path, $filename);
    }

    /**
     * Les valeur attendues sont les constantes définies dans la classe \Zend\Log\Logger
     *
     * @param int $priority
     */
    public function setPriority(int $priority)
    {
        $this->priority = $priority;
    }

    private function init()
    {
        if (empty($this->filelog)) {
            throw new \Exception(
                'Erreur de programmation : Le nom du fichier log n\'a  pas été initialisé.');
        }
        $filter = new Log\Filter\Priority($this->priority);
        $writer = new Log\Writer\Stream($this->filelog);
        $writer->addFilter($filter);
        $this->logger = new Log\Logger();
        $this->logger->addWriter($writer);
    }
}