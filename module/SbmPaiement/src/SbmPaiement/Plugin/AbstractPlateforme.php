<?php
/**
 * Classe abstraite pour définir les plugins
 *
 * Un plugin doit être installé dans un sous-répertoire du dossier Plugin, sous le nom dont il sera reconnu.
 * Sa configuration sera faite :
 * - soit dans le /config/autoload/sbm.local.php
 * - soit dans le /config/autoload/sbm.global.php
 * - soit dans le sous-répertoire config de son dossier
 * La clé dans les fichiers de configuration sera écrite en minuscule.
 * 
 * @project sbm
 * @package SbmPaiement/Plugin
 * @filesource AbstractPlateforme.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 août 2016
 * @version 2016-2.2.0
 */
namespace SbmPaiement\Plugin;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\Log\Writer\Stream;
use Zend\Log\Filter\Priority;
use Zend\Log\Logger;
use Zend\Stdlib\Parameters;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Validator\PlageIp;

abstract class AbstractPlateforme implements FactoryInterface, EventManagerAwareInterface, PlateformeInterface
{

    /**
     * Event manager
     *
     * @var EventManager
     */
    private $eventManager;

    /**
     * Db manager
     *
     * @var ServiceLocatorInterface
     */
    private $db_manager;

    /**
     * Configuration de la plateforme
     *
     * @var array
     */
    private $config = [];

    /**
     * Nom du fichier log
     *
     * @var string
     */
    private $filelog;

    /**
     * Nom de la plateforme de paiement (correspond à un plugin cad à un sous-répertoire de SbmPaiement\Model)
     *
     * @var string
     */
    private $plateforme;

    /**
     *
     * @var unknown
     */
    private $logger;

    /**
     * Données reçues, traitées et transmises par le plugin
     *
     * @var array
     */
    protected $data;

    /**
     * Données préparées pour enregistrer un paiement
     *
     * @var array
     */
    protected $paiement;

    /**
     * Données préparées pour valider les fiches dans scolarites
     *
     * @var array
     */
    protected $scolarite;

    /**
     * Numéro d'erreur
     *
     * @var int
     */
    protected $error_no = 0;

    /**
     * Messages d'erreur
     *
     * @var string
     */
    protected $error_msg = '';

    /**
     * Cette méthode est appelée à la fin de la méthode createService().
     * createService() lit la configuration enregistrée dans les fichiers de configuration standard ZF2 :
     * /config/autoload/sbm.global.php
     * /config/autoload/sbm.local.php
     * /module/SbmPaiement/config/module.config.php
     *
     * init() pourra lire ou inclure le fichier de configuration propre à chaque plugin qui se trouve
     * dans le dossier config du plugin. Par exemple : /module/SbmPaiement/src/SbmPaiement/Model/SystemPay/config/systempay.config.php
     *
     * Après cet appel, la propriété config devra contenir toute la configuration.
     */
    abstract protected function init();

    /**
     * Cette méthode est appelée par la méthode notification() pour controler la validité de la notification.
     * Cela peut être un contrôle de la signature ou une analyse du contenu de la notification.
     * S'il y a un problème, les propriétés error_no (n° d'erreur) et error_msg (message d'erreur) seront renseignées.
     * Si un traitement est nécessaire sur les données reçues, les données sous leur nouveau format seront placées dans
     * la propriété data de l'objet.
     */
    abstract protected function validNotification(Parameters $data);

    /**
     * Analyse le contenu de la propriété data pour savoir si le paiement a été réalisé par la plateforme.
     * S'il a échoué, les propriétés error_no (n° d'erreur) et error_msg (message d'erreur) seront renseignées.
     * Si un traitement est nécessaire sur les données reçues, les données sous leur nouveau format seront placées dans
     * la propriété data de l'objet, en remplacement des données initiales. En particulier, la référence de la commande
     * sera analysée pour retrouver les éléments qu'elle contient. (En effet, la référence peut dépendre des contraintes
     * de la plateforme)
     */
    abstract protected function validPaiement();

    /**
     * Si le paiement est valide, il faut préparer les données en vue de l'envoie d'un évènement qui permettra le traitement
     * dans les tables scolarites et paiements.
     * Voici les clés du tableau à fournir dans la propriété paiement :
     * - type : DEBIT ou CREDIT
     * - paiement : tableau contenant
     * - datePaiement
     * - dateValeur
     * - responsableId
     * - anneeScolaire
     * - exercice
     * - montant
     * - codeModeDePaiement
     * - codeCaisse
     * - reference
     * Voici la composition de la propriété scolarites
     * - type : DEBIT ou CREDIT
     * - millesime
     * - eleveIds (tableau des eleveId concernés)
     */
    abstract protected function prepareData();

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\EventManager\EventManagerAwareInterface::setEventManager()
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->addIdentifiers([
            'SbmPaiement\Plugin\Plateforme'
        ]);
        $this->eventManager = $eventManager;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\EventManager\EventsCapableInterface::getEventManager()
     */
    public function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->setEventManager(new EventManager());
        }
        
        return $this->eventManager;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db_manager = $serviceLocator->get('Sbm\DbManager');
        $config_paiement = StdLib::getParamR([
            'sbm',
            'paiement',
        ], $serviceLocator->get('config'), []);
        $this->plateforme = StdLib::getParam('plateforme', $config_paiement);
        $class = __NAMESPACE__ . '\\' . $this->plateforme . '\Plateforme';
        if (is_null($this->plateforme) || ! class_exists($class)) {
            throw new Exception('Mauvaise configuration de la plateforme de paiement dans le fichier de configuration.');
        }
        $this->config = StdLib::getParam(strtolower($this->plateforme), $config_paiement);
        $this->filelog = StdLib::concatPath(StdLib::getParam('path_filelog', $config_paiement), strtolower($this->plateforme) . '_error.log');
        // initialisation particulière de la classe dérivée
        $this->init();        
        
        return $this;
    }

    /**
     * Renvoie le db manager permettant d'accéder à la base de données
     * 
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getDbManager()
    {
        return $this->db_manager;
    }

    /**
     * Indique si l'adresse REMOTE_ADDR est autorisée
     *
     * @param string $remote_adress            
     *
     * @return boolean
     */
    public function isAuthorizedRemoteAdress($remote_adress)
    {
        $validator = new PlageIp([
            'range' => $this->getAuthorizedIp()
        ]);
        return $validator->isValid($remote_adress);
    }

    /**
     * Vérification de REMOTE_ADDR puis vérification propre à la plateforme.
     *
     * - si bon, lance un évènement 'paiementNotification' avec comme `target` le ServiceManager
     * et comme `argv` le tableau des data préparé par la méthode validNotification()
     * - si mauvais, enregistre l'appel dans un fichier log en précisant l'url de l'appel
     * et enfin, retourne un message de compte-rendu.
     *
     * La méthode validNotification($data) peut affecter la propriété $data (à partir du paramètre passé).
     * Si elle ne le fait pas, la propriété est affectée après la validation.
     *
     * Les méthodes validNotification($data) et validPaiement() affectent les propriétés $error (int) et $error_msg (string).
     * S'il n'y a pas d'erreur, $error_no == 0 et $error_msg == '' (initialisé à la construction)
     *
     * @param Parameters $data
     *            données transmises en POST
     * @param string $remote_addr
     *            adresse IP de l'appel
     * @return <b>string|false</b> renvoie false si l'adresse REMOTE_ADDR n'est pas autorisée
     */
    public function notification(Parameters $data, $remote_addr = '')
    {
        unset($this->data);
        if ($this->isAuthorizedRemoteAdress($remote_addr)) {
            if ($this->validNotification($data)) {
                if (! isset($this->data))
                    $this->data = $data;
                if ($this->validPaiement()) {
                    // log en INFO puis lance un évènement 'paiementOK' avec $this->data en paramètre
                    $this->logError(Logger::INFO, $this->error_no ? $this->error_msg : 'Paiement OK', $data);
                    $this->prepareData();
                    $this->getEventManager()->trigger('paiementOK', null, $this->paiement);
                    $this->getEventManager()->trigger('scolariteOK', null, $this->scolarite);
                    return 'Notification reçue le ' . date('d/m/Y à H/i/s') . ' (UTC).';
                } else {
                    // log en NOTICE puis lance un évènement 'paiementKO' avec $this->data en paramètre
                    $this->logError(Logger::NOTICE, 'Paiement KO : ' . $this->error_msg, $data);
                    $this->getEventManager()->trigger('paiementKO', null, $this->data);
                    return 'Notification reçue le ' . date('d/m/Y à H/i/s') . ' (UTC).';
                }
            } else {
                // log en ERR puis lance un évènement 'notificationError' avec $data en paramètre
                $this->logError(Logger::ERR, $this->error_msg, $data);
                $this->getEventManager()->trigger('notificationError', null, $data);
                return "Notification incorrecte reçue le " . date('d/m/Y à H/i/s') . ' (UTC).';
            }
        } else {
            // log en WARN puis lance un évènement 'notificationForbidden'
            $this->logError(Logger::WARN, 'Notification interdite: Adresse IP non autorisée', [
                $remote_addr,
                $data
            ]);
            $this->getEventManager()->trigger('notificationForbidden', null, [
                $remote_addr,
                $data
            ]);
            return false;
        }
    }

    /**
     * Affecte la propriété
     *
     * @param array $config            
     */
    protected function setConfig($config)
    {
        $this->config = $config;
    }

    protected function dumpConfig()
    {
        die(var_dump($this->config));
    }

    /**
     * Renvoie le paramètre de config indiqué par la clé
     *
     * @param string|array $key
     *            la clé
     *            
     * @throws Exception
     * @return \SbmCommun\Model\mixed
     */
    protected function getParam($key)
    {
        $key = (array) $key;
        if (! StdLib::array_keys_exists($key, $this->config)) {
            $propriete = count($key) == 1 ? current($key) : print_r($key, true);
            throw new Exception("Mauvaise configuration de la plateforme de paiement. La propriété '$propriete' n'est pas définie dans " . print_r($this->config, true));
        }
        return StdLib::getParamR($key, $this->config);
    }

    /**
     * Renvoie le nom de la plateforme
     *
     * @return string
     */
    protected function getPlateformeName()
    {
        return $this->plateforme;
    }

    /**
     * Renvoie les Ip autorisées à lancer une notification
     *
     * @return string|array
     */
    protected function getAuthorizedIp()
    {
        return StdLib::getParam('authorized_ip', $this->config);
    }

    /**
     * Renvoie la configuration de la plateforme
     *
     * @return array
     */
    protected function getPlateformeConfig()
    {
        return $this->config;
    }

    /**
     * Donne le millesime
     *
     * @return int|string
     */
    protected function getMillesime()
    {
        return Session::get('millesime', date('Y'));
    }

    /**
     * Donne l'année scolaire courante
     *
     * @return string
     */
    protected function getAnneeScolaire()
    {
        return Session::get('as')['libelle'];
    }

    /**
     * Donne l'exercice budgétaire
     *
     * @return string
     */
    protected function getExercice()
    {
        return date('Y');
    }

    /**
     * Renvoie le code du mode de paiement par CB
     * 
     * @return integer
     */
    protected function getCodeModeDePaiement()
    {
        $table = $this->getDbManager()->get('Sbm\Db\System\Libelles');
        return $table->getCode('ModeDePaiement', 'CB');
    }

    /**
     * Renvoie le code de la caisse
     *
     * @return integer
     */
    protected function getCodeCaisse()
    {
        $table = $this->getDbManager()->get('Sbm\Db\System\Libelles');
        return $table->getCode('Caisse', 'DFT');
    }

    /**
     * Méthode d'écriture dans un fichier log
     *
     * @param int $niveau
     *            niveau de l'error_reporting (ajustable dans le fichier de configuration)
     * @param string $message
     *            message de l'enregistrement
     * @param array $array
     *            contenu des data (ou remote_addr, data)
     */
    public function logError($niveau, $message, $array = [])
    {
        if (empty($this->logger)) {
            $filter = new Priority(StdLib::getParam('error_reporting', $this->config, Logger::WARN));
            $writer = new Stream($this->filelog);
            $writer->addFilter($filter);
            $this->logger = new Logger();
            $this->logger->addWriter($writer);
        }
        $this->logger->log($niveau, $message, $array);
    }

    /**
     * Réponses normalisées du centre d'autorisation
     *
     * @param string $code            
     */
    protected function getReponseMessage($code)
    {
        $nomenclature = [
            '00' => 'Transaction approuvée ou traitée avec succès.',
            '02' => 'Contacter l\'émetteur de carte.',
            '03' => 'Accepteur invalide.',
            '04' => 'Conserver la carte.',
            '05' => 'Ne pas honorer.',
            '07' => 'Conserver la carte, conditions spéciales.',
            '08' => 'Approuver après identification.',
            '12' => 'Transaction invalide.',
            '13' => 'Montant invalide.',
            '14' => 'Numéro de porteur invalide.',
            '15' => 'Emetteur de carte inconnu.',
            '17' => 'Annulation acheteur.',
            '19' => 'Répéter la transaction ultérieurement.',
            '20' => 'Réponse erronée (erreur dans le domaine serveur).',
            '24' => 'Mise à jour de fichier non supportée.',
            '25' => 'Impossible de localiser l\'enregistrement dans le fichier.',
            '26' => 'Enregistrement dupliqué, ancien enregistrement remplacé.',
            '27' => 'Erreur en « edit » sur champ de liste à jour fichier.',
            '28' => 'Accès interdit au fichier.',
            '29' => 'Mise à jour impossible.',
            '30' => 'Erreur de format.',
            '31' => 'Identifiant de l\'organisme acquéreur inconnu.',
            '33' => 'Date de validité de la carte dépassée.',
            '34' => 'Suspicion de fraude.',
            '38' => 'Date de validité de la carte dépassée.',
            '41' => 'Carte perdue.',
            '43' => 'Carte volée.',
            '51' => 'Provision insuffisante ou crédit dépassé.',
            '54' => 'Date de validité de la carte dépassée.',
            '55' => 'Code confidentiel erroné.',
            '56' => 'Carte absente du fichier.',
            '57' => 'Transaction non permise à ce porteur.',
            '58' => 'Transaction interdite au terminal.',
            '59' => 'Suspicion de fraude.',
            '60' => 'L\'accepteur de carte doit contacter l\'acquéreur.',
            '61' => 'Montant de retrait hors limite.',
            '63' => 'Règles de sécurité non respectées.',
            '68' => 'Réponse non parvenue ou reçue trop tard.',
            '75' => 'Nombre d’essais code confidentiel dépassé.',
            '76' => 'Porteur déjà en opposition, ancien enregistrement conservé.',
            '90' => 'Arrêt momentané du système.',
            '91' => 'Emetteur de cartes inaccessible.',
            '94' => 'Transaction dupliquée.',
            '96' => 'Mauvais fonctionnement du système.',
            '97' => 'Échéance de la temporisation de surveillance globale.',
            '98' => 'Serveur indisponible routage réseau demandé à nouveau.',
            '99' => 'Incident domaine initiateur.'
        ];
        return StdLib::getParam($code, "$code $nomenclature", "$code Code réponse inconnu.");
    }
}