<?php
/**
 * Plugin pour la plateforme de paiement PayBox
 *
 * @project sbm
 * @package SbmPaiement/Plugin/PayBox
 * @filesource Plateforme.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmPaiement\Plugin\PayBox;

use PhpOffice\PhpSpreadsheet\IOFactory;
use SbmBase\Model\Session;
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
     * @var string
     */
    private $refdet;

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
     * Plugin/PayBox/config/paybox.config.php
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
        $this->clearRefDet();
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
        foreach ($this->facture->getResultats()->getListeEleves() as $row) {
            $this->elevesIds[] = $row['eleveId'];
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
        if ($params['PBX_TOTAL'] < $this->getParam('montantmini')) {
            throw new Exception(
                'Le montant du est inférieur au montant minimal pour un paiement par CB.');
        } elseif ($params['PBX_TOTAL'] > $this->getParam('montantmaxi')) {
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
        if (! $this->refdet) {
            $cmd = sprintf("%4d%4s%14s%07d%07d%02d", $this->millesime, $this->exercice,
                date('YmdHis'), $this->facture->getNumero(),
                $this->responsable->responsableId, $this->nbEnfants);
            if ($this->paiement3fois) {
                $abonnement = $this->getFormulaireAbonnement();
                $abonnement['PBX_2MONT'] = sprintf('%010d', $this->getMontantAbonnement());
                foreach ($abonnement as $key => $value) {
                    $cmd .= $key . $value;
                }
            }
            $this->refdet = $cmd;
        }
        return $this->refdet;
    }

    private function clearRefDet()
    {
        $this->refdet = '';
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
        $form = new Formulaire('Formulaire', [
            'hiddens' => array_keys($this->variables)
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
        foreach ($serveurs as $url) {
            $parts = parse_url($url);
            $serveur = sprintf('%s://%s/load.html', $parts['scheme'], $parts['host']);
            $doc = new \DOMDocument();
            if (! @$doc->loadHTMLFile($serveur)) {
                continue;
            }
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
            return $url;
        }
        $message = 'Serveur Paybox indisponible.';
        $this->logError(Logger::INFO,
            sprintf($message . ' %s %s (%d - %s)', $this->responsable->nom,
                $this->responsable->prenom, $this->responsable->responsableId,
                $this->responsable->email));
        throw new \SbmPaiement\Plugin\Exception($message);
    }

    /**
     * PARTIE 2 : NOTIFICATION DE PAIEMENT MISE AU POINT JUSQU'ICI
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */

    /**
     * La référence se trouve dans la propriété $data (voir parent), clé 'ref'. Retrouve
     * et renvoie une partie dans la référence. Lorsqu'il n' a pas d'abonnement les clés
     * 'PBX_2MONT', 'PBX_NBPAIE', 'PBX_FREQ', 'PBX_QUAND' et 'PBX_DELAIS' renvoient une
     * chaine vide. Les autres clés n'étant pas obligatoire, la présence d'un abonnement
     * se traduit par un retour non vide pour 'PBX_2MONT'
     *
     * @param string $part
     * @throws Exception
     * @return string
     */
    public function getFromRefDet(string $part)
    {
        $refdet = $this->data['ref'];
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
     * @see \SbmPaiement\Plugin\AbstractPlateforme::validPostNotification()
     */

    /**
     * Surcharge pour renvoyer une page vide et traiter les reconductions
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\AbstractPlateforme::notification()
     */
    public function notification(string $method, Parameters $data, $remote_addr = '')
    {
        try {
            if ($method == 'post') {
                parent::notification($method, $data, $remote_addr);
            } else {
                // traitement de la reconduction
                if ($this->isAuthorizedRemoteAdress($remote_addr)) {
                    if ($this->validGetNotification($data)) {
                        if (! isset($this->data))
                            $this->data = $data;
                        if ($this->validReconduction()) {
                            $this->logError(Logger::INFO, 'Reconduction OK', $data);
                            $this->enregistrePaybox();
                        } else {
                            $this->logError(Logger::NOTICE,
                                'Reconduction KO : ' . $this->error_msg, $data);
                            $this->enregistreIncident();
                        }
                    } else {
                        $this->logError(Logger::ERR,
                            'Notification de reconduction incorrecte', $data);
                        $this->getEventManager()->trigger('notificationError', null, $data);
                    }
                } else {
                    // log en WARN puis lance un évènement 'notificationForbidden'
                    $this->logError(Logger::WARN,
                        'Accès GET interdit: Adresse IP non autorisée',
                        [
                            $remote_addr,
                            $data
                        ]);
                    $this->getEventManager()->trigger('notificationForbidden', null,
                        [
                            $remote_addr,
                            $data
                        ]);
                }
            }
        } catch (\Exception $e) {
            $this->logError(Logger::CRIT, $e->getMessage(), [
                $e->getTraceAsString()
            ]);
        }
        return '';
    }

    /**
     */
    private function annulerAbonnement()
    {
    }

    /**
     * DEBUG (uniquement en local)
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\AbstractPlateforme::isAuthorizedRemoteAdress()
     */
    public function isAuthorizedRemoteAdress($remote_adress)
    {
        if (getenv('APPLICATION_ENV') == 'development') {
            return true;
        }
        return parent::isAuthorizedRemoteAdress($remote_adress);
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

    protected function validNotification(Parameters $data)
    {
        $ok = $data->offsetExists('erreur') && $data->offsetExists('ref') &&
            $data->offsetExists('montant') && $data->offsetExists('sign');
        if ($ok) {
            // verif signature
        }
        return $ok;
    }

    private function validGetNotification(Parameters $data)
    {
        $ok = $data->offsetExists('ETAT_PBX') && $data->offsetExists('erreur') &&
            $data->offsetExists('ref') && $data->offsetExists('montant') &&
            $data->offsetExists('sign');
        if ($ok) {
            // verif ETAT_PBX
            $ok = $data->get('ETAT_PBX') == 'PBX_RECONDUCTION_ABT';
            // verif signature
        }
        return $ok;
    }

    protected function validPaiement()
    {
        if ($this->data->offsetExists('auto') && $this->data->get('erreur') == '00000') {
            $ok = true;
            $this->enregistrePaybox();
        } else {
            $ok = false;
        }
        return $ok;
    }

    protected function validReconduction()
    {
        if ($this->data->offsetExists('auto') && $this->data->get('erreur') == '00000') {
            $this->verifiePaiement();
            $ok = true;
        } else {
            $ok = false;
            $this->enregistreIncident();
        }
        return $ok;
    }

    protected function enregistrePaybox()
    {
        $table = $this->getDbManager()->get('SbmPaiement\Plugin\Table');
        if ($this->data->offsetExists('auto')) {
            $auto = $this->data->get('auto');
        } else {
            $auto = 'erreur ' . $this->data->get('erreur');
        }
        $array = [
            'responsableId' => $this->getFromRefDet('responsableId'),
            'exercice' => $this->getFromRefDet('exercice'),
            'numero' => $this->getFromRefDet('numero'),
            'auto' => $auto,
            'montant' => $this->data->get('montant'),
            'ref' => $this->data->get('ref'),
            'idtrans' => $this->data->get('idtrans'),
            'datetrans' => $this->data->get('datetrans'),
            'heuretrans' => $this->data->get('heuretrans'),
            'carte' => $this->data->get('carte'),
            'bin6' => $this->data->get('bin6'),
            'bin2' => $this->data->get('bin2'),
            'pays' => $this->data->get('pays'),
            'ip' => $this->data->get('ip')
        ];
        $obj = $table->getObjData();
        $obj->exchangeArray($array);
        try {
            $table->saveRecord($obj);
        } catch (\Exception $e) {
            $this->logError(Logger::CRIT, $e->getMessage());
        }
    }

    /**
     * Pour les reconductions (abonnements) lorsqu'il n'y a pas d'erreur
     */
    protected function verifiePaiement()
    {
        ;
    }

    /**
     * Pour les reconductions (abonnements) lorsqu'il y a une erreur (mettant fin à
     * l'abonnement)
     */
    protected function enregistreIncident()
    {
        $ref = $this->data->get('ref');
        // marque les abonnements comme impayés
        $tappels = $this->db_manager->get('Sbm\Db\Table\Appels');
        $resultset = $tappels->fetchAll([
            'refdet' => $ref
        ]);
        $tscolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $in = [];
        foreach ($resultset as $appel) {
            $in[] = $appel->eleveId;
        }
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->in('eleveId', $in);
        $set = [
            'paiementR1' => 0
        ];
        $tscolarites->getTableGateway()->update($set, $where);
        // résilie les échéances à partir de la datetrans
        $datetrans = \DateTime::createFromFormat('dmY', $this->data->get('datetrans'))->format(
            'Y-m-d');
        $where = new Where();
        $set = [
            'dateRefus' => $datetrans,
            'mouvement' => 0,
            'note' => 'Abonnement résilié. Echéance refusée. Erreur n° ' .
            $this->data->get('erreur')
        ];
        $where->like('reference', $ref)->greaterThanOrEqualTo('dateValeur', $datetrans);
        $tpaiements = $this->db_manager->get('Sbm\Db\Table\Paiements');
        $tpaiements->getTableGateway()->update($set, $where);
        // enregistre la notification
        $this->enregistrePaybox();
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
        $responsableId = (int) $this->getFromRefDet('responsableId');
        $millesime = (int) $this->getFromRefDet('millesime');
        $ladateP = \DateTime::createFromFormat('dmYH:i:s',
            $this->data['datetrans'] . $this->data['heuretrans']);
        $exercice = (int) $this->getFromRefDet('exercice');
        // $numeroF = (int) $this->getFromRefDet('numero');
        // $nbEnfants = (int) $this->getFromRefDet('nbEnfants');
        $pbx_2mont = (int) $this->getFromRefDet('PBX_2MONT');
        try {
            $pbx_nbpaie = (int) $this->getFromRefDet('PBX_NBPAIE');
        } catch (Exception $e) {
            $pbx_nbpaie = 0;
        }
        try {
            $pbx_freq = (int) $this->getFromRefDet('PBX_FREQ');
        } catch (Exception $e) {
            $pbx_freq = 0;
        }
        try {
            $pbx_quand = (int) $this->getFromRefDet('PBX_QUAND');
        } catch (Exception $e) {
            $pbx_quand = 0;
        }
        try {
            $pbx_delais = (int) $this->getFromRefDet('PBX_DELAIS');
        } catch (Exception $e) {
            $pbx_delais = 0;
        }
        $arrayPaiements = [];
        $paiement = [
            'datePaiement' => $ladateP->format('Y-m-d H:i:s'),
            'dateValeur' => $ladateP->format('Y-m-d'),
            'responsableId' => $responsableId,
            'anneeScolaire' => sprintf('%d-%d', $millesime, $millesime + 1),
            'exercice' => $exercice,
            'montant' => $this->data['montant'],
            'codeModeDePaiement' => $this->getCodeModeDePaiement(),
            'codeCaisse' => $this->getCodeCaisse(),
            'reference' => $this->data['ref']
        ];
        $arrayPaiements[] = $paiement;
        if ($pbx_2mont) {
            if ($pbx_delais) {
                $date1 = $ladateP->modify(
                    sprintf('+%d Day%s', $pbx_delais, $pbx_delais > 1 ? 's' : ''));
            } elseif ($pbx_quand) {
                $date1 = $ladateP->format('Y-m') . '-' . $pbx_quand;
                if ($date1 < $ladateP->format('Y-m-d')) {
                    $date1->modify('+1 Month');
                }
            } else {
                $date1 = $ladateP->modify('+1 Month');
            }
            $modif_date = sprintf('+%d Month%s', $pbx_freq, $pbx_freq > 1 ? 's' : '');
            for ($p = 1; $p <= $pbx_nbpaie; $p ++) {
                $paiement['dateValeur'] = $date1->format('Y-m-d');
                $paiement['montant'] = $pbx_2mont;
                $arrayPaiements[] = $paiement;
                $date1->modify($modif_date);
            }
        }
        $this->paiement = [
            'type' => 'DEBIT',
            'paiement' => $arrayPaiements
        ];
        $this->scolarite = [
            'type' => 'DEBIT',
            'millesime' => $this->millesime,
            'responsableId' => $responsableId,
            'eleveIds' => []
        ];
        $tAppels = $this->getDbManager()->get('Sbm\Db\Table\Appels');
        $rowset = $tAppels->fetchAll([
            'refdet' => $this->data['ref']
        ]);
        if ($rowset->count()) {
            // récupère les eleveId et coche les fiches de la table appels
            foreach ($rowset as $row) {
                $this->scolarite['eleveIds'][] = $row->eleveId;
            }
            $tAppels->markNotified($row->idOp); // tous à la fois
        }
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
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\PlateformeInterface::rapprochement()
     */
    public function rapprochement(array $data): array
    {
        $xlsfile = $data['xlsfile']['tmp_name'];
        $firstline = $data['firstline'];
        $spreadsheet = IOFactory::load($xlsfile);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        $cr = [];
        $table = $this->getDbManager()->get('SbmPaiement\Plugin\Table');
        foreach ($sheetData as $ligne) {
            if ($firstline) {
                // première ligne d'en-tête
                $firstline = false;
                continue;
            }
            if ($ligne['G'] != 'Débit') {
                // remboursement
                continue;
            }
            if ($ligne['H'] == 'Refusée' && $ligne['U'] == '3D secure') {
                // 3D secure refusé
                continue;
            }
            if ($ligne['H'] == 'Acceptée' && $ligne['U'] == 'Abonnement') {
                // abonnement accepté - vérifier que la référence du paiement est présente
                $cas = 'abonnement';
            } elseif ($ligne['U'] == 'Abonnement') {
                // soit un abonnement refusé, soit un paiement accepté
                $cas = 'résiliation';
            } else {
                $cas = 'paiement';
            }
            // transaction manquante ?
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $ligne['C']);
            $this->data = [
                'montant' => $ligne['E'] * 100,
                'ref' => $ligne['D'],
                'idtrans' => $ligne['A'],
                'datetrans' => $date->format('dmY'),
                'heuretrans' => $date->format('H:i:s'),
                'g3ds' => $ligne['V'],
                'carte' => $ligne['J'],
                'bin6' => '',
                'bin2' => '',
                'pays' => $ligne['L'],
                'ip' => $ligne['K']
            ];
            $responsableId = $this->getFromRefDet('responsableId');
            $exercice = $this->getFromRefDet('exercice'); // exercice, numero est la PK
            $numero = $this->getFromRefDet('numero'); // numéro facture
            switch ($cas) {
                case 'abonnement':
                    $where = new Where();
                    $where->like('ref', $ligne['D'] . 'PBX_2MONT%');
                    $resultset = $table->fetchAll($where);
                    if ($resultset->count()) {
                        // abonnement présent dans les tables
                        continue 2;
                    } else {
                        // aller chercher la facture pour avoir le montant total à payer
                        $this->data['auto'] = $ligne['AH'];
                        $this->paiement3fois = true;
                        $this->setFacture(
                            $this->getDbManager()
                                ->get('Sbm\Db\Table\Factures')
                                ->getRecord(
                                [
                                    'exercice' => $exercice,
                                    'numero' => $numero
                                ]));
                        $apayer = $this->getMontantAbonnement();
                        $abonnement = $this->getFormulaireAbonnement();
                        $abonnement['PBX_2MONT'] = sprintf('%010d', $apayer);
                        foreach ($abonnement as $key => $value) {
                            $this->data['ref'] .= $key . $value;
                        }
                    }
                    $this->data = new Parameters($this->data);
                    $this->enregistrePaybox();
                    break;
                case 'resiliation':
                    $where = new Where();
                    $where->like('ref', $ligne['D'] . 'PBX_2MONT%');
                    $resultset = $table->fetchAll($where);
                    if (! $resultset->count()) {
                        // l'abonnement n'est pas présent donc rien à annuler
                        continue 2;
                    }
                    if ($ligne['R']) {
                        $this->data['erreur'] = $this->getErreurCode($ligne['R']);
                    } else {
                        $this->data['erreur'] = '?????';
                    }
                    $this->data['ref'] = $resultset->current()['ref'];
                    $this->data = new Parameters($this->data);
                    $this->enregistreIncident();
                    break;
                default:
                    $resultset = $table->fetchAll([
                        'idtrans' => $ligne['A']
                    ]);
                    if ($resultset->count()) {
                        // transaction présente dans la table
                        continue 2;
                    }
                    $this->data['auto'] = $ligne['AH'];
                    $this->data = new Parameters($this->data);
                    $this->enregistrePaybox();
                    break;
            }
            $responsable = $this->getDbManager()
                ->get('Sbm\Db\Table\Responsables')
                ->getRecord($responsableId);
            $cr[] = [
                $cas,
                $ligne['C'],
                $ligne['E'],
                $responsableId,
                sprintf('%s %s', $responsable->nom, $responsable->prenom),
                $ligne['D'],
                $ligne['A']
            ];
        }
        return $cr;
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
     * Appelée par SbmPaiement\Controller\IndexConfroller::majnotificationAction() Pas
     * dans Paybox
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
        $header = [];
        $column = new \StdClass();
        $column->label = 'Nature';
        $column->align = 'L';
        $column->width = 15;
        $column->format = new Formatage();
        $header[] = $column;
        $column = new \StdClass();
        $column->label = 'Date';
        $column->align = 'L';
        $column->width = 20;
        $column->format = new Formatage();
        $header[] = $column;
        $column = new \StdClass();
        $column->label = 'Montant';
        $column->align = 'R';
        $column->width = 15;
        $column->format = new Formatage();
        $header[] = $column;
        $column = new \StdClass();
        $column->label = 'Id acheteur';
        $column->align = 'L';
        $column->width = 12;
        $column->format = new Formatage();
        $header[] = $column;
        $column = new \StdClass();
        $column->label = 'Acheteur';
        $column->align = 'L';
        $column->width = 45;
        $column->format = new Formatage();
        $header[] = $column;
        $column = new \StdClass();
        $column->label = 'Commande';
        $column->align = 'L';
        $column->width = 50;
        $column->format = new Formatage();
        $header[] = $column;
        $column = new \StdClass();
        $column->label = 'Transaction';
        $column->align = 'L';
        $column->width = 15;
        $column->format = new Formatage();
        $header[] = $column;
        return $header;
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
            '00015' => '00015: Paiement déjà effectué.',
            '00016' => '00016: Abonné déjà existant.',
            '00021' => '00021: Carte non autorisée.',
            '00029' => '00029: Carte non conforme.',
            '00030' => '00030: Temps d\'attente trop long.',
            '00033' => '00033: Pays non autorisé.',
            '00040' => '00040: Opération sans authentification 3-D Secure, bloquée par le filtre.',
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
            '00199' => '00199: Autorisation refusée  incident domaine initiateur.',
            '99999' => '99999: Opération en attente de validation par l\'émetteur du moyen de paiement.'
        ];
    }

    private function getErreurCode(string $message): string
    {
        foreach ($this->getErreurMessages() as $code => $description) {
            if (strpos($description, $message) !== false) {
                return $code;
            }
        }
        return '?????';
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