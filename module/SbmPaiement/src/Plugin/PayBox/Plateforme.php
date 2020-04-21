<?php
/**
 * Plugin pour la plateforme de paiement PayBox
 *
 * @TODO : à reprendre en entier avec la doc de Paybox
 *
 * @project sbm
 * @package SbmPaiement/Plugin/PayBox
 * @filesource Plateforme.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmPaiement\Plugin\PayBox;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Paiements\FactureInterface as Facture;
use SbmFront\Model\Responsable\Responsable;
use SbmPaiement\Plugin;
use SbmPaiement\Plugin\Exception;
use Zend\Db\Sql\Where;
use Zend\Log\Logger;
use Zend\Stdlib\Parameters;

class Plateforme extends Plugin\AbstractPlateforme implements Plugin\PlateformeInterface
{

    /**
     *
     * @var int
     */
    private $millesime;

    /**
     *
     * @var string
     */
    private $exercice;

    /**
     *
     * @var string
     */
    private $idOp;

    /**
     *
     * @var \SbmCommun\Model\Paiements\FactureInterface
     */
    private $facture;

    /**
     *
     * @var \SbmFront\Model\Responsable\Responsable
     */
    private $responsable;

    /**
     *
     * @var array
     */
    private $eleveIds = [];

    /**
     *
     * @var int
     */
    private $nbEnfants;

    /**
     * Grain de sel
     *
     * @var string
     */
    private $sel;

    /**
     *
     * @var bool
     */
    private $paiement3fois;

    /**
     *
     * @var int
     */
    private $montantAbonnement;

    /**
     *
     * @var array
     */
    private $variables;

    /**
     * Fusionne les config du plugin trouvées dans sbm.local.php et dans
     * Plugin/PayBox/config/paybox.config.php TODO: à adapter au fur et à mesure
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\AbstractPlateforme::init()
     */
    protected function init()
    {
        $this->setConfig(
            array_merge($this->getPlateformeConfig(),
                include __DIR__ . '/config/paybox.config.php'));
        $this->millesime = Session::get('millesime');
        $this->exercice = date('Y');
        $this->facture = null;
        $this->responsable = null;
        $this->nbEnfants = 0;
        $this->sel = 'eclipse';
        $this->paiement3fois = false;
    }

    /*
     * PARTIE 1 : PAIEMENT PAR APPEL DE LA PLATEFORME PAYBOX A L'AIDE D'UN FORMULAIRE
     */

    /**
     * Récupère le responsable en session
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\PlateformeInterface::setResponsable()
     */
    public function setResponsable(Responsable $responsable)
    {
        $this->responsable = $responsable;
        return $this;
    }

    /**
     * Uniquement si $nb vaut 3
     *
     * @param int $nb
     * @return \SbmPaiement\Plugin\PayBox\Plateforme
     */
    public function setPaiement3Fois(int $nb)
    {
        $this->paiement3fois = $nb == 3;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\PlateformeInterface::prepare()
     */
    public function prepare()
    {
        if (empty($this->facture)) {
            // génère une facture ou la récupère si elle existe déjà
            $this->setFacture(
                $this->db_manager->get('Sbm\Facture')
                    ->setResponsableId($this->responsable->responsableId)
                    ->facturer());
        }
        // préparation des paramètres pour la méthode prepareAppel()
        $this->elevesIds = [];
        foreach ($this->facture->getResultats()->getListeEleves('a_facturer') as $eleveId => $row) {
            $this->elevesIds[] = $eleveId;
            unset($row);
        }
        $this->nbEnfants = count($this->elevesIds);
        return $this;
    }

    /**
     * Initialise la propriété facture
     *
     * @param \SbmCommun\Model\Paiements\FactureInterface $facture
     * @return \SbmPaiement\Plugin\PayBox\Plateforme
     */
    public function setFacture(Facture $facture)
    {
        $this->facture = $facture;
        return $this;
    }

    /**
     * Appelée par la méthode SbmPaiement\Controller\IndesController::formulaireAction()
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\PlateformeInterface::initPaiement()
     */
    public function initPaiement()
    {
        $params = $this->prepareAppel();
        if ($params['montant'] < $this->getParam('montantmini')) {
            throw new Exception(
                'Le montant du est inférieur au montant minimal pour un paiement par CB.');
        } elseif ($params['montant'] > $this->getParam('montantmaxi')) {
            throw new Exception(
                'Le montant du est supérieur au montant maximal pour un paiement par CB.');
        }
        try {
            $this->idOp = $this->getUniqueId([]);
            $this->enregistreAppel();
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\RuntimeException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Prépare les paramètres d'appel de la procédure creerPaiementSecurise du web service
     *
     * @return array
     */
    private function prepareAppel()
    {
        $this->variables = $this->getFormulaireConfigVariables();
        $mode = $this->getParam('mode');
        $cle = $this->getParam([
            'identifiant',
            $mode,
            'CLE'
        ]);
        $this->variables['PBX_SITE'] = $this->getParam(
            [
                'identifiant',
                $mode,
                'PBX_SITE'
            ]);
        $this->variables['PBX_RANG'] = $this->getParam(
            [
                'identifiant',
                $mode,
                'PBX_RANG'
            ]);
        $this->variables['PBX_IDENTIFIANT'] = $this->getParam(
            [
                'identifiant',
                $mode,
                'PBX_IDENTIFIANT'
            ]);
        $this->variables['PBX_TOTAL'] = $this->getMontantCentimes();
        $this->variables['PBX_CMD'] = $this->getRefDet();
        $this->variables['PBX_PORTEUR'] = $this->responsable->email;
        $this->variables['PBX_REPONDRE_A'] = $this->getParam(
            [
                'notification',
                'url_notification'
            ]);
        $this->variables['PBX_EFFECTUE'] = $this->getParam(
            [
                'url_retour',
                'PBX_EFFECTUE'
            ]);
        $this->variables['PBX_REFUSE'] = $this->getParam([
            'url_retour',
            'PBX_REFUSE'
        ]);
        $this->variables['PBX_ANNULE'] = $this->getParam([
            'url_retour',
            'PBX_ANNULE'
        ]);
        $this->variables['PBX_ATTENTE'] = $this->getParam([
            'url_retour',
            'PBX_ATTENTE'
        ]);
        $this->variables['PBX_TIME'] = date("c");
        // calcul HMAC
        $array = [];
        foreach ($this->variables as $key => &$value) {
            $array[] = "$key=$value";
        }
        $message = implode('&', $array);
        $binKey = pack("H*", $cle);
        $hmac = strtoupper(hash_hmac('sha512', $message, $binKey));
        $this->variables['PBX_HMAC'] = $hmac;
        return $this->variables;
    }

    /**
     * Exemple : string(38) "20202021201906131259590000001000000201" ou string(90)
     * "20202021201906131259590000001000000201PBX_2MONT0000000300PBX_NBPAIE02PBX_FREQ01PBX_QUAND00"
     *
     * @return string
     */
    public function getRefDet()
    {
        $cmd = sprintf("%4d%4s%14s%07d%07d%02d", $this->millesime, $this->exercice,
            date('YmdHis'), $this->facture->getNumero(), $this->responsable->responsableId,
            $this->nbEnfants);
        if ($this->paiement3fois) {
            $abonnement = $this->getFormulaireAbonnement();
            $abonnement['PBX_2MONT'] = sprintf('%010d', $this->getMontantAbonnement());
            foreach ($abonnement as $key => $value) {
                $cmd .= $key . $value;
            }
        }
        return $cmd;
    }

    /**
     * Renvoie le montant à payer en centimes. Si paiement en 3 fois, calcule le premier
     * paiement (direct) et prépare les versements différés dans montantAbonnement
     *
     * @return number
     */
    private function getMontantCentimes(): int
    {
        $montant = $this->facture->getResultats()->getSolde() * 100;
        if ($this->paiement3fois) {
            $this->montantAbonnement = round($montant / 3, 0);
            $montant -= $this->montantAbonnement * 2;
        } else {
            $this->montantAbonnement = 0;
        }
        return $montant;
    }

    /**
     * Renvoie le montant d'un abonnement. S'il est nul, lance le calcul
     * getMontantCentimes (pour éviter que l'odre des calculs soit source d'erreur)
     *
     * @return number
     */
    private function getMontantAbonnement(): int
    {
        if (! $this->montantAbonnement) {
            $this->getMontantCentimes();
        }
        return $this->montantAbonnement;
    }

    /**
     * Renvoie le timestamp sur 10 caractères
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\PlateformeInterface::getUniqueId()
     */
    public function getUniqueId(array $params)
    {
        return sprintf('%010d', time());
    }

    /**
     * Enregistre l'appel à paiement de cet object
     */
    private function enregistreAppel()
    {
        $tAppels = $this->db_manager->get('Sbm\Db\Table\Appels');
        $odata = $tAppels->getObjData();
        foreach ($this->elevesIds as $eleveId) {
            $odata->exchangeArray(
                [
                    'refdet' => $this->getRefDet(),
                    'idOp' => $this->idOp,
                    'responsableId' => $this->responsable->responsableId,
                    'eleveId' => $eleveId,
                    'montant' => $this->getMontantCentimes()
                ]);
            $tAppels->saveRecord($odata);
        }
    }

    /**
     * Prépare le formulaire d'appel de la plateforme et charge les valeurs à envoyer.
     * Cette méthode est appelée dans la view formulaire.phtml du plugin.
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\PlateformeInterface::getForm()
     */
    public function getForm()
    {
        $form = new Formulaire('Formulaire',
            [
                'hiddens' => array_keys($this->getVariables())
            ]);
        $form->setAttribute('action', $this->getUrl())
            ->setData($this->variables);
        return $form;
    }

    /**
     * Renvoie l'URL de paiement proposée à l'usager pour accéder au paiement en ligne
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\PlateformeInterface::getUrl()
     */
    public function getUrl()
    {
        return $this->urlDispo($this->getParam('urlpaiement'));
    }

    /**
     * Vérifie qu'un serveur est disponible. Sinon lance une exception.
     *
     * @param array $serveurs
     * @throws \SbmPaiement\Plugin\Exception
     * @return string
     */
    private function urlDispo(array $serveurs): string
    {
        $serveurOK = false;
        foreach ($serveurs as $serveur) {
            $doc = new \DOMDocument();
            $doc->loadHTMLFile('https://' . $serveur . '/load.html');
            $server_status = "";
            $element = $doc->getElementById('server_status');
            if ($element) {
                $server_status = $element->textContent;
            }
            if ($server_status == 'OK') {
                $serveurOK = true;
                break;
            }
        }
        if ($serveurOK) {
            return $serveur;
        }
        throw new \SbmPaiement\Plugin\Exception('Serveur Paybox indisponible.');
    }

    /**
     * PARTIE 2 : NOTIFICATION DE PAIEMENT MISE AU POINT JUSQU'ICI
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */

    /**
     * Retrouve une partie dans refdet. Lorsqu'il n' a pas d'abonnement les clés
     * 'PBX_2MONT', 'PBX_NBPAIE', 'PBX_FREQ', 'PBX_QUAND' et 'PBX_DELAIS' renvoient une
     * chaine vide. Les autres clés n'étant pas obligatoire, la présence d'un abonnement
     * se traduit par un retour non vide pour 'PBX_2MONT'
     *
     * @param string $part
     * @param string $refdet
     * @throws Exception
     * @return string
     */
    public function getFromRefDet(string $part, string $refdet)
    {
        switch ($part) {
            case 'millesime':
                $result = substr($refdet, 0, 4);
                break;
            case 'exercice':
                $result = substr($refdet, 4, 4);
                break;
            case 'date':
                $result = substr($refdet, 8, 14);
                break;
            case 'numero':
                $result = substr($refdet, 22, 7);
                break;
            case 'responsableId':
                $result = substr($refdet, 29, 7);
                break;
            case 'nbEnfants':
                $result = substr($refdet, 36, 2);
                break;
            case 'PBX_2MONT':
                if (strlen($refdet) == 90 && strpos($refdet, 'PBX_2MONT')) {
                    $result = substr($refdet, 47, 10);
                } else {
                    $result = '';
                }
                break;
            default:
                $abonnement = $this->getFormulaireAbonnement();
                if (array_key_exists($part, $abonnement)) {
                    if (strlen($refdet) == 90) {
                        $pos = strpos($refdet, $part) + strlen($part);
                        $len = $part == 'PBX_DELAIS' ? 3 : 2;
                        $result = substr($refdet, $pos, $len);
                    } else {
                        $result = '';
                    }
                } else {
                    throw new Exception('Clé inconnue dans refdet.');
                }
                break;
        }
        return $result;
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\AbstractPlateforme::validNotification()
     */
    protected function validNotification(Parameters $data)
    {
        $ok = $data->offsetExists('erreur') && $data->offsetExists('ref') &&
            $data->offsetExists('montant') && $data->offsetExists('sign');
        if ($ok) {
            // verif signature
        }
        return $ok;
    }

    protected function validPaiement()
    {
        $ok = $this->data->offsetExists('auto') && $this->data->get('erreur') == '00000';
        return $ok;
    }

    /**
     * Cette méthode prépare les données à inclure dans les tables paiements et
     * scolarites. Ces données sont rangées dans les propriétés de même nom.
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\AbstractPlateforme::prepareData()
     */
    protected function prepareData()
    {
    }

    /**
     * Renvoie le code de la caisse (surcharge de la méthode)
     *
     * @return integer
     */
    protected function getCodeCaisse()
    {
        $table = $this->getDbManager()->get('Sbm\Db\System\Libelles');
        return $table->getCode('Caisse', 'Paybox');
    }

    /**
     * PARTIE 3 : RAPPROCHEMENT DES PAIEMENTS AVEC LE BORDEREAU DE PAYBOX
     */
    /**
     * TODO: à écrire lorsqu'on traitera l'action rapprochementAction()
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\PlateformeInterface::rapprochement()
     */
    public function rapprochement(string $csvname, bool $firstline, string $separator,
        string $enclosure, string $escape): array
    {
    }

    /**
     * Vérifie si des appels du responsable enregistré sont non notifiés et si c'est le
     * cas prépare le plugin puis lance le rattrapage de ces notifications. Appelé par le
     * SbmParent\Controller\IndexController::indexAction() pour s'assurer que les
     * notifications ont été reçues. Non prévu dans Paybox. Laissé vide.
     *
     * @return void
     */
    public function checkPaiement()
    {
    }

    /**
     * Retourne la valeur de la variable si son nom est donné sinon le tableau des
     * variables
     *
     * @param string $name
     *            Valable pour les variables du formulaire de nom PBX_DEVISE, PBX_RETOUR,
     *            PBX_HASH et PBX_RUF1
     * @return string|array
     */
    private function getFormulaireConfigVariables(string $name = null)
    {
        if ($name) {
            return $this->getParam([
                'formulaire',
                'variables',
                $name
            ]);
        } else {
            return $this->getParam([
                'formulaire',
                'variables'
            ]);
        }
    }

    /**
     * Retourne le tableau des paramètres à inclure dans PBX_CMD pour définir un
     * abonnement
     *
     * @return array
     */
    private function getFormulaireAbonnement()
    {
        return $this->getParam([
            'formulaire',
            'abonnement'
        ]);
    }

    /**
     * Appelée par SbmPaiement\Controller\IndexConfroller::majnotificationAction() TODO: à
     * écrire
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\PlateformeInterface::majnotification()
     */
    public function majnotification(array $args)
    {
    }

    public function validFormAbandonner(Parameters $post)
    {
    }

    public function rapprochementCrHeader(): array
    {
    }

    /**
     * Surcharge pour renvoyer une page vide
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\AbstractPlateforme::notification()
     */
    public function notification(Parameters $data, $remote_addr = '')
    {
        parent::notification($data, $remote_addr);
        return '';
    }

    /**
     * Renvoie le tableau des adressses autorisées (adresses de Paybox)
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\AbstractPlateforme::getAuthorizedIp()
     */
    protected function getAuthorizedIp()
    {
        try {
            $mode = $this->getParam('mode');
        } catch (\Exception $e) {
            $mode = 'TEST';
        }
        return $this->getParam([
            'notification',
            'authorized_ip',
            $mode
        ]);
    }

    /**
     * Renvoie une liste pour pour initialiser un Select
     *
     * @param string $erreur
     * @return array|string|boolean
     */
    public static function selectErreurListe(string $erreur = null)
    {
        if (is_null($erreur)) {
            return array_merge(
                [
                    'tous' => 'Toutes les transactions',
                    'non' => 'Toutes les transactions réussies',
                    'oui' => 'Toutes les transactions en échec'
                ], self::getErreurMessages());
        } elseif (array_key_exists($erreur, self::getErreurMessages())) {
            return self::getErreurMessages()[$erreur];
        } else {
            return false;
        }
    }

    /**
     * Liste des erreurs de Paybox
     *
     * @return string[]
     */
    private static function getErreurMessages()
    {
        return [
            '00000' => '00000: Opération réussie.',
            '00001' => '00001: La connexion au centre d\'autorisation a échoué.',
            '00003' => '00003: Erreur Paybox',
            '00004' => '00004: Numéro de porteur ou cryptogramme visuel invalide.',
            '00006' => '00006: Accès refusé  site/rang/identifiant incorrects.',
            '00008' => '00008: Date de validité incorrecte.',
            '00009' => '00009: Erreur de création d\'un abonnement.',
            '00010' => '00010: Devise inconnue.',
            '00011' => '00011: Montant incorrect.',
            '00015' => '00015: paiement déjà effectué.',
            '00016' => '00016: Abonné déjà existant.',
            '00021' => '00021: Carte non autorisée.',
            '00029' => '00029: Carte non conforme.',
            '00030' => '00030: Temps d\'attente trop long.',
            '00033' => '00033: Pays non autorisé.',
            '00101' => '00101: Autorisation refusée  contacter l\'émetteur de carte.',
            '00102' => '00102: Autorisation refusée  contacter l\'émetteur de carte.',
            '00103' => '00103: Autorisation refusée  commerçant invalide.',
            '00104' => '00104: Autorisation refusée  conserver la carte.',
            '00105' => '00105: Autorisation refusée  ne pas honorer.',
            '00107' => '00107: Autorisation refusée  conserver la carte, conditions spéciales.',
            '00108' => '00108: Autorisation refusée  approuver après identification du porteur.',
            '00112' => '00112: Autorisation refusée  transaction invalide.',
            '00113' => '00113: Autorisation refusée  montant invalide.',
            '00114' => '00114: Autorisation refusée  numéro de porteur invalide.',
            '00115' => '00115: Autorisation refusée  émetteur de carte inconnu.',
            '00117' => '00117: Autorisation refusée  annulation client.',
            '00119' => '00119: Autorisation refusée  répéter la transaction ultérieurement.',
            '00120' => '00120: Autorisation refusée  réponse erronée (erreur dans le domaine serveur).',
            '00124' => '00124: Autorisation refusée  mise à jour de fichier non supportée.',
            '00125' => '00125: Autorisation refusée  impossible de localiser l‟enregistrement dans le fichier.',
            '00126' => '00126: Autorisation refusée  enregistrement dupliqué, ancien enregistrement remplacé.',
            '00127' => '00127: Autorisation refusée  erreur en « edit » sur champ de mise à jour fichier.',
            '00128' => '00128: Autorisation refusée  accès interdit au fichier.',
            '00129' => '00129: Autorisation refusée  mise à jour de fichier impossible.',
            '00130' => '00130: Autorisation refusée  erreur de format.',
            '00131' => '00131: Autorisation refusée  identifiant de l’organisme acquéreur inconnu.',
            '00133' => '00133: Autorisation refusée  date de validité de la carte dépassée.',
            '00134' => '00134: Autorisation refusée  suspicion de fraude.',
            '00138' => '00138: Autorisation refusée  nombre d\'essais code confidentiel dépassé.',
            '00141' => '00141: Autorisation refusée  carte perdue.',
            '00143' => '00143: Autorisation refusée  carte volée.',
            '00151' => '00151: Autorisation refusée  provision insuffisante ou crédit dépassé.',
            '00154' => '00154: Autorisation refusée  date de validité de la carte dépassée.',
            '00155' => '00155: Autorisation refusée  code confidentiel erroné.',
            '00156' => '00156: Autorisation refusée  carte absente du fichier.',
            '00157' => '00157: Autorisation refusée  transaction non permise à ce porteur.',
            '00158' => '00158: Autorisation refusée  transaction interdite au terminal.',
            '00159' => '00159: Autorisation refusée  suspicion de fraude.',
            '00160' => '00160: Autorisation refusée  l\'accepteur de carte doit contacter l\'acquéreur.',
            '00161' => '00161: Autorisation refusée  dépasse la limite du montant de retrait.',
            '00163' => '00163: Autorisation refusée  règles de sécurité non respectées.',
            '00168' => '00168: Autorisation refusée  réponse non parvenue ou reçue trop tard.',
            '00175' => '00175: Autorisation refusée  nombre d\'essais code confidentiel dépassé.',
            '00176' => '00176: Autorisation refusée  porteur déjà en opposition, ancien enregistrement conservé.',
            '00189' => '00189: Autorisation refusée  échec de l\'authentification',
            '00190' => '00190: Autorisation refusée  arrêt momentané du système.',
            '00191' => '00191: Autorisation refusée  émetteur de cartes inaccessible.',
            '00194' => '00194: Autorisation refusée  demande dupliquée.',
            '00196' => '00196: Autorisation refusée  mauvais fonctionnement du système.',
            '00197' => '00197: Autorisation refusée  échéance de la temporisation de surveillance globale.',
            '00198' => '00198: Autorisation refusée  serveur innaccessible.',
            '00199' => '00199: Autorisation refusée  incident domaine initiateur.'
        ];
    }
}

class Reserve
{

    public function setFacture(Facture $facture): Facture
    {
        $this->facture = $facture;
        return $this;
    }

    public function getIdOp(): string
    {
        return $this->idOp;
    }

    public function getResponsable(): Responsable
    {
        return $this->responsable;
    }

    public function getFacture(): Facture
    {
        return $this->facture;
    }

    /**
     * Prépare le plugin en initialisant les propriétés facture, justificatifs des sommes
     * dues, nombre d'enfants concernés ...
     *
     * @return self
     */
    public function prepare()
    {
        if (empty($this->facture)) {
            // génère une facture ou la récupère si elle existe déjà
            $this->setFacture(
                $this->db_manager->get('Sbm\Facture')
                    ->setResponsableId($this->responsable->responsableId))
                ->facturer();
        }
        // préparation des paramètres pour la méthode prepareAppel()
        $this->elevesIds = [];
        foreach ($this->facture->getResultats()->getListeEleves() as $eleveId => $row) {
            if (! $row['paiementR1'] && ! $row['gratuit']) {
                $this->elevesIds[] = $eleveId;
            }
        }
        $this->nbEnfants = count($this->elevesIds);
        return $this;
    }

    /**
     * Prépare les paramètres de la requête paiement de PayBox, vérifie que le montant est
     * dans la plage autorisée pour un paiement par CB, lance la requête et inscrit la
     * demande de paiement dans la table appels
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\PlateformeInterface::initPaiement()
     */
    public function initPaiement()
    {
        $params = $this->prepareAppel();
        if ($params['montant'] < $this->getParam('montantmini')) {
            throw new Exception(
                'Le montant du est inférieur au montant minimal pour un paiement par CB.');
        } elseif ($params['montant'] > $this->getParam('montantmaxi')) {
            throw new Exception(
                'Le montant du est supérieur au montant maximal pour un paiement par CB.');
        }
        $this->idOp = $this->getUniqueId($params);
        if ($this->idOp) {
            try {
                $this->enregistreAppel();
            } catch (\SbmCommun\Model\Db\Service\Table\Exception\RuntimeException $e) {
                if ($this->rattrapageNotification($e->getMessage())) {
                    throw new Exception('Vous avez déjà payé. La base a été mise à jour.');
                } else {
                    throw new Exception($e->getMessage());
                }
            }
        }
    }

    /**
     * Vérifie si des appels du responsable enregistré sont non notifiés et si c'est le
     * cas prépare le plugin puis lance le rattrapage de ces notifications.
     *
     * @return void
     */
    public function checkPaiement()
    {
        $tAppels = $this->db_manager->get('Sbm\Db\Table\Appels');
        $where = new Where();
        $where->literal('notified = 0')
            ->equalTo('responsableId', $this->responsable->responsableId)
            ->like('refdet', $this->millesime . '%');
        $resultset = $tAppels->fetchAll($where);
        if ($resultset->count()) {
            $this->prepare();
            $array = [];
            // filtre les idOp pour en disposer de manière unique
            foreach ($resultset as $row) {
                $array[$row->idOp] = $row->idOp;
            }
            foreach ($array as $idOp) {
                $this->rattrapageNotification($idOp);
            }
        }
    }

    /**
     * Marque notified les fiches de la table des appels dont idOp est indiqué
     */
    private function marqueAppel()
    {
        $tAppels = $this->db_manager->get('Sbm\Db\Table\Appels');
        $tAppels->markNotified($this->idOp);
    }

    /**
     * Supprime l'appel à paiement correspondant à la propriété idOp de cet object Si
     * $nonNotified == true alors on ne supprime que les appels non notifiés
     * correspondants à idOp. Par défaut, supprime les appels notifiés ou non notifiés
     * indifféremment.
     *
     * @param boolean $nonNotified
     */
    private function supprimeAppel(bool $nonNotified = false)
    {
        if (empty($this->idOp)) {
            throw new Exception('idOp absent.');
        }
        $conditions = [
            'idOp' => $this->idOp
        ];
        if ($nonNotified) {
            $conditions['notified'] = 0;
        }
        $tAppels = $this->db_manager->get('Sbm\Db\Table\Appels');
        $tAppels->deleteRecord($conditions);
    }

    /**
     * Renvoie un ctrl construit à partir d'une chaine et du grain de sel
     *
     * @param string $s
     * @return string
     */
    private function getCtrl(string $s)
    {
        return md5($s . $this->sel);
    }

    /**
     * Vérifie si $ctrl est le codage de $idOp
     *
     * @param string $ctrl
     * @param string $idOp
     * @return boolean
     */
    private function validCtrl(string $ctrl, string $idOp)
    {
        return $ctrl == $this->getCtrl($idOp);
    }

    /**
     * Renvoie l'URL de paiement proposée à l'usager pour accéder au paiement en ligne
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\PlateformeInterface::getUrl()
     */
    public function getUrl()
    {
        return $this->getParam('urlpaiement') . $this->idOp;
    }

    /**
     * CCMGC TS 2018 2019 Exercice 2019 Facture 00001 Responsable 0000002 Date 20190613 01
     * Modèle ci-dessus
     *
     * @return string
     */
    public function getObjet()
    {
        return sprintf(
            "CCMGC TS %4d %4d Exercice %4s Facture %05d Responsable %07d Date %s %02d",
            $this->millesime, $this->millesime + 1, $this->exercice,
            $this->facture->getNumero(), $this->responsable->responsableId, date('Ymd'),
            $this->nbEnfants);
    }

    /**
     * Exemple : "201820190000100000022019061301"
     *
     * @return string
     */
    public function getRefDet()
    {
        return sprintf("%4d%4s%05d%07d%s%02d", $this->millesime, $this->exercice,
            $this->facture->getNumero(), $this->responsable->responsableId, date('Ymd'),
            $this->nbEnfants);
    }

    /**
     * Retrouve une partie dans refdet
     *
     * @param string $part
     * @param string $refdet
     * @throws Exception
     * @return string
     */
    public function getFromRefDet(string $part, string $refdet)
    {
        switch ($part) {
            case 'millesime':
                $result = substr($refdet, 0, 4);
                break;
            case 'exercice':
                $result = substr($refdet, 4, 4);
                break;
            case 'numero':
                $result = substr($refdet, 8, 5);
                break;
            case 'responsableId':
                $result = substr($refdet, 13, 7);
                break;
            case 'date':
                $result = substr($refdet, 20, 8);
                break;
            case 'nbEnfants':
                $result = substr($refdet, 28, 2);
            default:
                throw new Exception('Clé inconnue dans refdet.');
                break;
        }
        return $result;
    }

    /**
     *
     * @todo : mettre au point pour Paybox
     * @return boolean true si le paiement a été confirmé, false sinon
     */
    protected function validPaiement()
    {
        /*
         * $this->idOp = $this->data['idop']; $arguments = [ 'arg0' => [ 'idOp' =>
         * $this->idOp ] ]; try { $this->data->exchangeArray(
         * $this->soapRequest('recupererDetailPaiementSecurise', $arguments));
         * $this->marqueAppel(); if ($this->data['resultrans'] != 'P') { return false; }
         * $this->enregistrePaybox(); return true; } catch (Exception $e) { return false;
         * }
         */
        return false;
    }

    /**
     * Reçoit en argument un tableau de paramètres contenant l'une des clés suivantes :
     * idOp, responsableId ou eleveId. Interroge si nécessaire (responsableId ou eleveId)
     * la table des appels pour trouver les idOp non notifiés, puis lance le rattrapage
     * des notifications. Si le tableau de paramètres est vide, lance le rattrapage de
     * tous les appels non notifiés de l'année scolaire en cours.
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\PlateformeInterface::majnotification()
     */
    public function majnotification(array $args)
    {
        $tAppels = $this->db_manager->get('Sbm\Db\Table\Appels');
        $where = new Where();
        $idOps = [];
        if (array_key_exists('idOp', $args)) {
            $idOps = (array) $args['idOp'];
        } elseif (array_key_exists('responsableId', $args)) {
            // tout les appels non notifiés de l'année scolaire en cours pour ce
            // responsable
            $where->literal('notified = 0')
                ->equalTo('responsableId', $args['responsableId'])
                ->like('refdet', $this->millesime . '%');
            $resultset = $tAppels->fetchAll($where);
            if ($resultset->count()) {
                $idOps = [];
                // filtre les idOp pour en disposer de manière unique
                foreach ($resultset as $row) {
                    $idOps[$row->idOp] = $row->idOp;
                }
            }
        } elseif (array_key_exists('eleveId', $args)) {
            // tout les appels non notifiés de l'année scolaire en cours pour cet élève
            $where->literal('notified = 0')
                ->equalTo('eleveId', $args['eleveId'])
                ->like('refdet', $this->millesime . '%');
            $resultset = $tAppels->fetchAll($where);
            if ($resultset->count()) {
                $idOps = [];
                // filtre les idOp pour en disposer de manière unique
                foreach ($resultset as $row) {
                    $idOps[$row->idOp] = $row->idOp;
                }
            }
        } else {
            // tout les appels non notifiés de l'année scolaire en cours
            $where->literal('notified = 0')->like('refdet', $this->millesime . '%');
            $resultset = $tAppels->fetchAll($where);
            if ($resultset->count()) {
                $idOps = [];
                // filtre les idOp pour en disposer de manière unique
                foreach ($resultset as $row) {
                    $idOps[$row->idOp] = $row->idOp;
                }
            }
        }
        foreach ($idOps as $idOp) {
            $this->rattrapageNotification($idOp);
        }
    }

    /**
     * Vérifie s'il n'y aurait pas un paiement non notifié relatif à idOp ATTENTION !!!
     * L'index dans data est en minuscules.
     *
     * @param string $idOp
     *
     * @return boolean
     */
    private function rattrapageNotification(string $idOp)
    {
        $idOpSav = $this->idOp;
        $this->data = new Parameters([
            'idop' => $idOp
        ]);
        $ok = false;
        if ($this->validPaiement()) {
            $this->logError(Logger::INFO,
                $this->error_no ? $this->error_msg : 'Paiement OK', $this->data);
            $this->prepareData();
            $this->getEventManager()->trigger('paiementOK', null, $this->paiement);
            $this->getEventManager()->trigger('scolariteOK', null, $this->scolarite);
            $ok = true;
        } else {
            $this->logError(Logger::NOTICE, 'Paiement KO : ' . $this->error_msg,
                $this->data);
        }
        $this->idOp = $idOpSav;
        return $ok;
    }

    /**
     * Enregistre la notification dans la table paybox
     *
     * @todo : mettre au point pour Paybox
     * @throws Exception
     */
    protected function enregistrePaybox()
    {
        /*
         * $table = $this->getDbManager()->get('SbmPaiement\Plugin\Table'); if
         * ($this->responsable) { $titulaire = sprintf("%s %s", $this->responsable->nom,
         * $this->responsable->prenom); } else { $responsableId =
         * $this->getFromRefDet('responsableId', $this->data['refdet']); $tResponsable =
         * $this->db_manager->get('Sbm\Db\Table\Responsables'); $responsable =
         * $tResponsable->getRecord($responsableId); $titulaire = sprintf("%s %s",
         * $responsable->nomSA, $responsable->prenomSA); } $objectData =
         * $table->getObjData()->exchangeArray( array_merge($this->data->toArray(), [
         * 'payboxId' => null, 'idOp' => $this->idOp, 'titulaire' => $titulaire ])); try {
         * $table->saveRecord($objectData); $this->error_no = 0; } catch
         * (\Zend\Db\Adapter\Exception\InvalidQueryException $e) { while ($e && ! $e
         * instanceof \PDOException) { $e = $e->getPrevious(); } $code = $e->getCode(); if
         * ($code == 23000) { $this->error_msg = 'Duplicate Entry'; $this->error_no =
         * 23000; } else { throw new Exception( "Erreur #$code lors de l\'enregistrement
         * de la notification de paiement.", is_int($code) ? $code : 99999, $e); } }
         */
    }

    /**
     * Vérifie que le paramètre idop est dans les paramètres
     *
     * @return boolean
     */
    protected function validNotification(Parameters $data)
    {
        if ($data->offsetExists('idop')) {
            return true;
        }
        $this->error_msg = 'idop n\'a pas été trouvé !';
        return false;
    }

    /**
     * Vérifie que le paramètre post contient les clés 'ctrl' et 'idOp' et qu'elles sont
     * compatibles.
     *
     * @param \Zend\Stdlib\Parameters $post
     * @throws Exception
     */
    public function validFormAbandonner(Parameters $post)
    {
        if (! $post->offsetExists('ctrl') || ! $post->offsetExists('idOp') ||
            ! $this->validCtrl($post['ctrl'], $post['idOp'])) {
            $msg = 'Action interdite. L\'appel ne provient pas de la plateforme.';
            throw new Exception($msg);
        }
        $this->idOp = $post['idOp'];
        $this->supprimeAppel(true);
    }

    /**
     * Cette méthode prépare les données à inclure dans les tables paiements et
     * scolarites. Ces données sont rangées dans les propriétés de même nom.
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\AbstractPlateforme::prepareData()
     */
    protected function prepareData()
    {
        $ladate = \DateTime::createFromFormat('dmYHi',
            $this->data['dattrans'] . $this->data['heurtrans'])->format('Y-m-d H:i:s');
        $millesime = (int) $this->getFromRefDet('millesime', $this->data['refdet']);
        $this->paiement = [
            'type' => 'DEBIT',
            'paiement' => [
                'datePaiement' => $ladate,
                'dateValeur' => $ladate,
                'responsableId' => $this->getFromRefDet('responsableId',
                    $this->data['refdet']),
                'anneeScolaire' => sprintf('%d-%d', $millesime, $millesime + 1),
                'exercice' => $this->getFromRefDet('exercice', $this->data['refdet']),
                'montant' => $this->data['montant'],
                'codeModeDePaiement' => $this->getCodeModeDePaiement(),
                'codeCaisse' => $this->getCodeCaisse(),
                'reference' => $this->data['refdet']
            ]
        ];
        $this->scolarite = [
            'type' => 'DEBIT',
            'millesime' => $this->millesime,
            'eleveIds' => []
        ];
        $tAppels = $this->getDbManager()->get('Sbm\Db\Table\Appels');
        $rowset = $tAppels->fetchAll([
            'idOp' => $this->idOp
        ]);
        foreach ($rowset as $row) {
            $this->scolarite['eleveIds'][] = $row->eleveId;
        }
    }

    /**
     * Donne le nom complet du fichier wsdl. Il peut être en local ou en ligne mais il
     * faut que les fichiers xsd associés soient accessibles. Ici la configuration est en
     * local.
     *
     * @return string
     */
    protected function getWsdl()
    {
        // à modifier si on veut un wsdl en ligne
        return StdLib::concatPath(StdLib::findParentPath(__DIR__, 'config/wsdl'),
            'PaiementSecuriseService.wsdl');
    }

    protected function soapRequest(string $method, array $arguments)
    {
        $msg = '';
        try {
            $client = new \Zend\Soap\Client($this->getWsdl(),
                [
                    'soap_version' => SOAP_1_1,
                    'cache_wsdl' => WSDL_CACHE_NONE
                ]);
            $result = $client->{$method}($arguments);
            return $result->return;
        } catch (\SoapFault $s) {
            if (property_exists($s, 'detail')) {
                $msg = sprintf('[ERROR: [%s] %s - %s (%d) : %s', $s->faultcode,
                    $s->faultstring, $s->detail->FonctionnelleErreur->code,
                    $s->detail->FonctionnelleErreur->severite,
                    $s->detail->FonctionnelleErreur->libelle);
                $origine = sprintf('ligne %d du fichier %s', $s->getLine(), $s->getFile());
                $this->logError(\Zend\Log\Logger::WARN, $origine,
                    [
                        $s->faultcode,
                        $s->faultstring,
                        $s->detail->FonctionnelleErreur->code,
                        $s->detail->FonctionnelleErreur->severite,
                        $s->detail->FonctionnelleErreur->libelle
                    ]);
                $this->error_msg = sprintf('%s : %s',
                    $s->detail->FonctionnelleErreur->code,
                    $s->detail->FonctionnelleErreur->libelle);
                $this->error_no = 1;
            } else {
                $msg = sprintf('[ERROR: [%s] %s - client->{%s}(%s) : %s', $s->faultcode,
                    $s->faultstring, $method, json_encode($arguments), json_encode($s));
                $origine = sprintf('ligne %d du fichier %s', $s->getLine(), $s->getFile());
                $this->logError(\Zend\Log\Logger::WARN, $origine,
                    [
                        $s->faultcode,
                        $s->faultstring,
                        $method,
                        json_encode($arguments),
                        json_encode($s)
                    ]);
                $this->error_msg = sprintf('%s : %s', $s->faultcode, $s->faultstring);
                $this->error_no = 2;
            }
        } catch (\Exception $e) {
            $msg = sprintf('[ERROR: [%d] %s', $e->getCode(), $e->getMessage());
            $origine = sprintf('ligne %d du fichier %s', $e->getLine(), $e->getFile());
            $this->logError(\Zend\Log\Logger::ERR, $origine,
                [
                    $e->getCode(),
                    $e->getMessage()
                ]);
            $this->error_msg = $e->getMessage();
            $this->error_no = 2;
        }
        throw new Exception($msg);
    }

    /**
     * Reçoit un fichier csv de compte-rendu et renvoie les lignes de ce fichiers qui
     * n'ont pas de correspondance dans la table paybox.
     *
     * @todo : mettre au point pour PayBox
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\PlateformeInterface::rapprochement()
     */
    public function rapprochement(string $csvname, bool $firstline, string $separator,
        string $enclosure, string $escape): array
    {
        $tPaybox = $this->getDbManager()->get('SbmPaiement\Plugin\Table');
        $tResponsables = $this->getDbManager()->get('Sbm\Db\Table\Responsables');
        $fcsv = fopen($csvname, 'r');
        if ($firstline) {
            fgets($fcsv);
        }
        $cr = [];
        // die(var_dump($fcsv, 0, $separator, $enclosure, $escape));
        while (($ligne = fgets($fcsv)) !== false) {
            $data = explode($separator, $ligne);
            if ($data[0] != 'LIGNE') {
                // saute les lignes commençant par EN-TETE ou par PIED-DE-PAGE
                continue;
            }
            if ($firstline) {
                // saute la ligne d'en-tête des lignes commençant par LIGNE
                $firstline = false;
                continue;
            }
            if ($enclosure) {
                array_walk($data,
                    function (&$value, $key) use ($enclosure) {
                        $value = trim($value, $enclosure);
                    });
            }
            $dt = $data[1];
            $refdet = $data[2];
            $montant = $data[3];
            $results = $tPaybox->fetchAll([
                'refdet' => $refdet
            ]);
            $absent = $results->count() == 0;
            if ($absent) {
                $responsableId = (int) substr($refdet, 13, 7);
                try {
                    $oResponsable = $tResponsables->getRecord($responsableId);
                } catch (\Exception $e) {
                    $oResponsable = $tResponsables->getObjData();
                    $oResponsable->exchangeArray([
                        'nomSA' => '',
                        'prenomSA' => ''
                    ]);
                }
                $cr[] = [
                    $dt,
                    $oResponsable->nomSA,
                    $oResponsable->prenomSA,
                    $refdet,
                    $montant
                ];
            }
        }
        fclose($fcsv);
        return $cr;
    }

    /**
     * Renvoie un tableau d'entête de lignes d'un compte-rendu d'un rapprochement (doit
     * avoir autant d'élément qu'une ligne de cr)
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\PlateformeInterface::rapprochementCrHeader()
     */
    public function rapprochementCrHeader(): array
    {
        $header = [];
        $column = new \StdClass();
        $column->label = 'Date';
        $column->align = 'L';
        $column->width = 20;
        $column->format = new Formatage();
        $header[] = $column;
        $column = new \StdClass();
        $column->label = 'Nom';
        $column->align = 'L';
        $column->width = 40;
        $column->format = new Formatage();
        $header[] = $column;
        $column = new \StdClass();
        $column->label = 'Prénom';
        $column->align = 'L';
        $column->width = 40;
        $column->format = new Formatage();
        $header[] = $column;
        $column = new \StdClass();
        $column->label = 'Référence';
        $column->align = 'L';
        $column->width = 50;
        $column->format = new Formatage();
        $header[] = $column;
        $column = new \StdClass();
        $column->label = 'Montant';
        $column->align = 'R';
        $column->width = 20;
        $column->format = new Formatage();
        $header[] = $column;
        return $header;
    }
}

class Formatage
{

    public function __construct($callback = null)
    {
        $this->func_format($callback);
    }

    private function func_format($callbackOrData)
    {
        static $f;
        if (is_null($callbackOrData)) {
            $f = function ($data) {
                return $data;
            };
        } elseif (is_callable($callbackOrData)) {
            $f = $callbackOrData;
        } else {
            return $f($callbackOrData);
        }
    }

    public function __invoke($data)
    {
        return $this->func_format($data);
    }
}