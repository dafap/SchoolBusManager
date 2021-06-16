<?php
/**
 * Plugin pour la plateforme de paiement PayFiP
 *
 * @project sbm
 * @package SbmPaiement/Plugin/PayFiP
 * @filesource Plateforme.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 juin 2021
 * @version 2021-2.5.11
 */
namespace SbmPaiement\Plugin\PayFiP;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Form\ButtonForm;
use SbmCommun\Millau\Tarification\Facture\Facture;
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
     * @var \SbmCommun\Millau\Tarification\Facture\Facture
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
     *
     * @var string
     */
    private $idOp;

    /**
     * Grain de sel
     *
     * @var string
     */
    private $sel;

    /**
     * Fusionne les config du plugin trouvées dans sbm.local.php et dans
     * plugin/PayFiP/config/payfip.config.php
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\AbstractPlateforme::init()
     */
    protected function init()
    {
        $this->setConfig(
            array_merge($this->getPlateformeConfig(),
                include __DIR__ . '/config/payfip.config.php'));
        $this->millesime = Session::get('millesime');
        $this->exercice = date('Y');
        $this->facture = null;
        $this->responsable = null;
        $this->nbEnfants = 0;
        $this->sel = 'eclipse';
    }

    public function setResponsable(Responsable $responsable)
    {
        $this->responsable = $responsable;
        return $this;
    }

    public function setFacture(Facture $facture): Facture
    {
        $this->facture = $facture;
        return $facture;
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
                new \SbmCommun\Millau\Tarification\Facture\Facture($this->db_manager,
                    $this->db_manager->get(
                        \SbmCommun\Millau\Tarification\Facture\Calculs::class)
                        ->getResultats($this->responsable->responsableId)))
                ->facturer();
        }
        // préparation des paramètres pour la méthode prepareAppel()
        $this->elevesIds = [];
        foreach ($this->facture->getResultats()->getListeEleves() as $eleveId => $row) {
            if (! $row['paiement'] && ! $row['fa'] && ! $row['gratuit']) {
                $this->elevesIds[] = $eleveId;
            }
        }
        $this->nbEnfants = count($this->elevesIds);
        return $this;
    }

    /**
     * Prépare les paramètres de la requête creerPaiementSecurise de PayFiP, vérifie que
     * le montant est dans la plage autorisée pour un paiement par CB, lance la requête et
     * inscrit la demande de paiement dans la table appels
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
     * Enregistre l'appel à paiement de cet object
     */
    private function enregistreAppel()
    {
        if (empty($this->idOp)) {
            throw new Exception('idOp absent.');
        }
        $tAppels = $this->db_manager->get('Sbm\Db\Table\Appels');
        $odata = $tAppels->getObjData();
        foreach ($this->elevesIds as $eleveId) {
            $odata->exchangeArray(
                [
                    'refdet' => $this->getRefDet(),
                    'idOp' => $this->idOp,
                    'responsableId' => $this->responsable->responsableId,
                    'eleveId' => $eleveId
                ]);
            $tAppels->saveRecord($odata);
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
     * Prépare les données à partir de l'objet responsable et renvoie un formulaire
     * initialisé
     */
    public function getForm()
    {
        $form = new ButtonForm(
            [
                'idOp' => $this->idOp,
                'ctrl' => $this->getCtrl($this->idOp)
            ],
            [
                'payer' => [
                    'class' => 'confirm',
                    'formaction' => $this->getUrl(),
                    'value' => 'Payer'
                ],
                'abandonner' => [
                    'class' => 'confirm',
                    'value' => 'Abandonner'
                ]
            ], 'plugin-formulaire');
        return $form;
    }

    /**
     * Prépare les paramètres d'appel de la procédure creerPaiementSecurise du web service
     *
     * @return array
     */
    private function prepareAppel()
    {
        return [
            'exer' => date('Y'),
            'mel' => $this->responsable->email,
            'montant' => sprintf('%d', $this->facture->getResultats()->getSolde() * 100),
            'numcli' => $this->getParam('numcli'),
            'objet' => $this->getObjet(),
            'refdet' => $this->getRefDet(),
            'saisie' => $this->getParam('saisie')[$this->getParam('mode')],
            'urlnotif' => $this->getParam('urlnotification'),
            'urlredirect' => $this->getParam('urlredirect')
        ];
    }

    /**
     * Cette méthode appelle le webservice pour demander un idOp. Le idOp est de la forme
     * (36 caractères) : '4b0eb5b0-b335-11e2-9219-001fe256bdfe'
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\PlateformeInterface::getUniqueId()
     */
    public function getUniqueId(array $params)
    {
        $arguments = [
            'arg0' => $params
        ];
        $rep = $this->soapRequest('creerPaiementSecurise', $arguments);
        return $rep->idOp;
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
     * La propriété $this->data possède une clé 'idop'. Cette méthode interroge le web
     * service payFiP par la méthode recupererDetailPaiementSecurise et place le résultat
     * dans la propriété $this->data en conservant son type Parameters. Si le webservice
     * répond, les appels enregistrés avec cet idOp sont marqués notified. Si la réponse
     * confirme le paiement, la notification est enregistrée dans la table payfip.
     *
     * @return boolean true si le paiement a été confirmé, false sinon
     */
    protected function validPaiement()
    {
        $this->idOp = $this->data['idop'];
        $arguments = [
            'arg0' => [
                'idOp' => $this->idOp
            ]
        ];
        try {
            $this->data->exchangeArray(
                $this->soapRequest('recupererDetailPaiementSecurise', $arguments));
            $this->marqueAppel();
            if ($this->data['resultrans'] != 'P') {
                return false;
            }
            $this->enregistrePayfip();
            return true;
        } catch (Exception $e) {
            return false;
        }
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
     * Enregistre la notification dans la table payfip
     *
     * @throws Exception
     */
    protected function enregistrePayfip()
    {
        $table = $this->getDbManager()->get('SbmPaiement\Plugin\Table');
        if ($this->responsable) {
            $titulaire = sprintf("%s %s", $this->responsable->nom,
                $this->responsable->prenom);
        } else {
            $responsableId = $this->getFromRefDet('responsableId', $this->data['refdet']);
            $tResponsable = $this->db_manager->get('Sbm\Db\Table\Responsables');
            $responsable = $tResponsable->getRecord($responsableId);
            $titulaire = sprintf("%s %s", $responsable->nomSA, $responsable->prenomSA);
        }
        $objectData = $table->getObjData()->exchangeArray(
            array_merge($this->data->toArray(),
                [
                    'payfipId' => null,
                    'idOp' => $this->idOp,
                    'titulaire' => $titulaire
                ]));
        try {
            $table->saveRecord($objectData);
            $this->error_no = 0;
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            while ($e && ! $e instanceof \PDOException) {
                $e = $e->getPrevious();
            }
            $code = $e->getCode();
            if ($code == 23000) {
                $this->error_msg = 'Duplicate Entry';
                $this->error_no = 23000;
            } else {
                throw new Exception(
                    "Erreur #$code lors de l\'enregistrement de la notification de paiement.",
                    is_int($code) ? $code : 99999, $e);
            }
        }
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
     * n'ont pas de correspondance dans la table payfip.
     *
     * {@inheritdoc}
     * @see \SbmPaiement\Plugin\PlateformeInterface::rapprochement()
     */
    public function rapprochement(string $csvname, bool $firstline, string $separator,
        string $enclosure, string $escape): array
    {
        $tPayfip = $this->getDbManager()->get('SbmPaiement\Plugin\Table');
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
            $results = $tPayfip->fetchAll([
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