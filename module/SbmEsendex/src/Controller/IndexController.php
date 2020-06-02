<?php
/**
 * Controller de l'API Esendex
 *
 * Service d'envoi de SMS
 *
 * @project sbm
 * @package SbmEsendex/src/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\Http\PhpEnvironment\Response;
use Zend\Log\Logger;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\View\Model\ViewModel;
use Esendex;
use SbmBase\Model\DateLib;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $args = $this->initListe(
            [
                [
                    'name' => 'date',
                    'type' => 'Zend\Form\Element\Date',
                    'attributes' => [],
                    'options' => [
                        'label' => 'Envoyés le'
                    ]
                ],
                [
                    'name' => 'avant',
                    'type' => 'Zend\Form\Element\Date',
                    'attributes' => [],
                    'options' => [
                        'label' => 'Envoyés avant le'
                    ]
                ]
            ], null, null, [
                'avant' => 'expression:date < ?'
            ]);
        if ($args instanceof Response) {
            return $args;
        } elseif (array_key_exists('cancel', $args)) {
            $this->redirectToOrigin()->reset();
            return $this->redirect()->toRoute('sbmservicesms');
        }
        return new ViewModel(
            [
                'paginator' => $this->db_manager->get('Sbm\Db\Table\EsendexBatches')->paginator(
                    $args['where'], [
                        'date Desc'
                    ]),
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_esendexBatches',
                    20),
                'criteres_form' => $args['form']
            ]);
    }

    public function accountInfoAction()
    {
        try {
            return new ViewModel(
                [
                    'account_info' => $this->api_sms->getAccounts(),
                    'page' => $this->params('page', 1)
                ]);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage(
                'Veuillez vérifier la configuration du compte Esendex.');
            $this->redirect()->toRoute('sbmservicesms');
        }
    }

    /**
     * Bilan des envois d'un batch précisé en POST dans le paramètre 'batchid'
     */
    public function bilanAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
        }
        if (array_key_exists('cancel', $args) || ! array_key_exists('batchid', $args)) {
            return $this->redirect()->toRoute('sbmservicesms',
                [
                    'action' => 'index',
                    'page' => $currentPage
                ]);
        }
        Session::set('post', $args, $this->getSessionNamespace());
        try {
            $data = $this->api_sms->getMessagesBatch($args['batchid']);
        } catch (\Exception $e) {
            $data = [
                [
                    'Erreur' => 'Le lot n\'a pas été trouvé.'
                ]
            ];
        }
        return new ViewModel([
            'data' => $data,
            'page' => $this->params('page', 1)
        ]);
    }

    /**
     * Liste des destinataires des envois pour le batch précisé en POST
     */
    public function destinatairesAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            if (array_key_exists('cancel', $prg)) {
                return $this->redirect()->toRoute('sbmservicesms',
                    [
                        'action' => 'index',
                        'page' => $currentPage
                    ]);
            } elseif (array_key_exists('esendexbatchId', $prg)) {
                Session::set('post', $prg, $this->getSessionNamespace());
            }
            $args = $prg;
        }
        $where = [
            'esendexbatchId' => $args['esendexbatchId']
        ];
        return new ViewModel(
            [
                'paginator' => $this->db_manager->get('Sbm\Db\Table\EsendexTelephones')->paginator(
                    $where),
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage(
                    'nb_esendexTelephones', 20)
            ]);
    }

    public function toAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
        }
        if (array_key_exists('cancel', $args) || ! array_key_exists('telephone', $args)) {
            return $this->redirect()->toRoute('sbmservicesms',
                [
                    'action' => 'destinataires',
                    'page' => $currentPage
                ]);
        }
        Session::set('post', $args, $this->getSessionNamespace());
        try {
            $data = $this->api_sms->getMessagesHeader($args['telephone']);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
            return $this->redirect()->toRoute('sbmservicesms',
                [
                    'action' => 'destinataires',
                    'page' => $currentPage
                ]);
        }
        return new ViewModel([
            'data' => $data,
            'page' => $this->params('page', 1)
        ]);
    }

    public function voirBodyAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
        }
        if (array_key_exists('cancel', $args) || ! array_key_exists('bodyUri', $args)) {
            return $this->redirect()->toRoute('sbmservicesms',
                [
                    'action' => 'destinataires',
                    'page' => $currentPage
                ]);
        }
        Session::set('post', $args, $this->getSessionNamespace());
        try {
            $data = $this->api_sms->getMessageBody($args['bodyUri']);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
            return $this->redirect()->toRoute('sbmservicesms',
                [
                    'action' => 'destinataires',
                    'page' => $currentPage
                ]);
        }
        return new ViewModel([
            'data' => $data,
            'page' => $this->params('page', 1)
        ]);
    }

    /**
     * Suppression d'un batch et des fiches associées dans esendexsms et esendextelephones
     */
    public function supprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new \SbmCommun\Form\ButtonForm([
            'id' => null
        ],
            [
                'supproui' => [
                    'class' => 'confirm',
                    'value' => 'Confirmer'
                ],
                'supprnon' => [
                    'class' => 'confirm',
                    'value' => 'Abandonner'
                ]
            ]);
        $params = [
            'data' => [
                'alias' => 'Sbm\Db\Table\EsendexBatches',
                'id' => 'esendexbatchId'
            ],
            'form' => $form
        ];
        try {
            $r = $this->supprData($params,
                function ($id, $tEsendexBatches) {
                    return [
                        'id' => $id,
                        'data' => $tEsendexBatches->getRecord($id)
                    ];
                });
        } catch (\Exception $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer cet envoi.');
            return $this->redirect()->toRoute('sbmservicesms',
                [
                    'action' => 'index',
                    'page' => $currentPage
                ]);
        }

        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmservicesms',
                        [
                            'action' => 'index',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    // die(var_dump($r->getResult()));
                    return new ViewModel(
                        [
                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'esendexbatchId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    /**
     * En attente : pour les envois différés
     */
    public function envoiDiffereAction()
    {
        return $this->retour(FlashMessenger::NAMESPACE_INFO,
            'Cette fonction n\'est pas encore développée.');
    }

    /**
     * Demande d'envoi provenant d'un groupe
     */
    public function envoiGroupeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', [], $this->getSessionNamespace());
            if (empty($args)) {
                return $this->retour(FlashMessenger::NAMESPACE_WARNING,
                    'Aucun SMS envoyé.');
            }
            $initRedirectToOrigin = false;
        } else {
            $args = $prg;
            if (! array_key_exists('page', $args)) {
                $args['page'] = $this->params('page', 1);
            }
            Session::set('post', $args, $this->getSessionNamespace());
            $initRedirectToOrigin = true;
        }
        $oTelephones = new \SbmGestion\Model\Communication\Telephones($args);
        if ($initRedirectToOrigin) {
            $this->redirectToOrigin()->setBack($oTelephones->getUrlBack());
        }
        if ($oTelephones->getFilterName() == 'circuit') {
            $oCircuit = $this->db_manager->get('Sbm\Db\Table\Circuits')->getRecord(
                $oTelephones->getFilterValue());
            $oTelephones->setQueryParams(
                [
                    'ligneId' => $oCircuit->ligneId,
                    'sens' => $oCircuit->sens,
                    'moment' => $oCircuit->moment,
                    'ordre' => $oCircuit->ordre,
                    'stationId' => $oCircuit->stationId
                ]);
        }
        $qTelephones = $this->db_manager->get('Sbm\Db\Query\Responsable\Telephones');
        $resultset = $qTelephones->{$oTelephones->getQueryMethod()}(
            $oTelephones->getQueryParam());
        $arrayTo = [];
        foreach ($resultset as $row) {
            if ($row['telephoneF']) {
                $arrayTo[$row['telephoneF']] = $row['to'];
            }
            if ($row['telephoneP']) {
                $arrayTo[$row['telephoneP']] = $row['to'];
            }
            if ($row['telephoneT']) {
                $arrayTo[$row['telephoneT']] = $row['to'];
            }
        }
        $nbDemandes = count($arrayTo);
        if (! $nbDemandes) {
            return $this->retour(FlashMessenger::NAMESPACE_WARNING,
                'Aucun numéro de téléphone trouvé. Pas d\'envoi.');
        }
        $credits = $this->api_sms->getCredits();
        $body = StdLib::getParam('body', $args, false);
        $coutUnitaire = $body ? $this->api_sms->getCout($body) : 1;
        if ($nbDemandes * $coutUnitaire > $credits) {
            return $this->retour(FlashMessenger::NAMESPACE_WARNING,
                'Crédit insuffisant. Veuillez acheter des SMS ou contacter Esendex via support@esendex.fr');
        }
        Session::set('aTo', $arrayTo, 'servicesms\sendns');
        $form = $this->form_manager->get(\SbmEsendex\Form\Sms::class);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmservicesms', [
                'action' => 'send'
            ]))
            ->setData($args);
        $view = new ViewModel(
            [
                'form' => $form->prepare(),
                'destinataires' => $arrayTo,
                'telephones' => array_keys($arrayTo),
                'credits' => $credits
            ]);
        return $view->setTemplate('sbm-esendex/index/envoi-sms.phtml');
    }

    /**
     * Le formulaire doit renvoyer sur la route 'servicesms/send'
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function envoiSmsAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            if (array_key_exists('url1_retour', $args)) {
                $this->redirectToOrigin()->setBack($args['url1_retour']);
                unset($args['url1_retour']);
                Session::set('post', $args, $this->getSessionNamespace());
            } else {
                // on récupère les telephones en session
                $post = Session::get('post', [], $this->getSessionNamespace());
                $args['telephones'] = $post['telephones'];
            }
        }
        $telephones = StdLib::getParam('telephones', $args, []);
        $nbDemandes = count($telephones);
        if ($nbDemandes < 1 || array_key_exists('cancel', $args)) {
            return $this->retour(FlashMessenger::NAMESPACE_WARNING, 'Aucun SMS envoyé.');
        }
        $credits = $this->api_sms->getCredits();
        // lorsqu'on envoie un lien on le trouve dans 'contenu'
        $body = StdLib::getParam('body', $args, StdLib::getParam('contenu', $args, false));
        $coutUnitaire = $body ? $this->api_sms->getCout($body) : 1;
        if ($nbDemandes * $coutUnitaire > $credits) {
            return $this->retour(FlashMessenger::NAMESPACE_WARNING,
                'Crédit insuffisant. Veuillez acheter des SMS ou contacter Esendex via support@esendex.fr');
        }
        $destinataire = StdLib::getParam('responsable', $args, '');
        $arrayTo = array_fill_keys($telephones, $destinataire);
        Session::set('aTo', $arrayTo, 'servicesms\sendns');
        $form = $this->form_manager->get(\SbmEsendex\Form\Sms::class);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmservicesms', [
                'action' => 'send'
            ]))
            ->setData($args);
        return new ViewModel(
            [
                'form' => $form->prepare(),
                'destinataires' => (array) $destinataire,
                'telephones' => $telephones,
                'credits' => $credits
            ]);
    }

    public function sendAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->retour(FlashMessenger::NAMESPACE_WARNING, 'Aucun SMS envoyé.');
        }
        // arrayTo : tableau de la forme [telephone => destinataire, ...]
        $arrayTo = Session::get('aTo', [], 'servicesms\sendns');
        $nbDemandes = count($arrayTo);
        $body = StdLib::getParam('body', $prg, false);
        if (! $body || ! $nbDemandes || array_key_exists('cancel', $prg)) {
            return $this->retour(FlashMessenger::NAMESPACE_WARNING, 'Aucun SMS envoyé.');
        }
        $credits = $this->api_sms->getCredits();
        $coutUnitaire = $body ? $this->api_sms->getCout($body) : 1;
        if ($nbDemandes * $coutUnitaire > $credits) {
            return $this->retour(FlashMessenger::NAMESPACE_WARNING,
                'Crédit insuffisant. Veuillez acheter des SMS ou contacter Esendex via support@esendex.fr');
        }
        $form = $this->form_manager->get(\SbmEsendex\Form\Sms::class);
        $form->setData($prg);
        if ($form->isValid()) {
            $date_envoi = DateLib::nowToMysql();
            try {
                // envoyer doit recevoir un tableau de telephones
                $reponse = $this->api_sms->envoyer(array_keys($arrayTo),
                    $form->getData()['body']);
                $batchid = StdLib::getParam('batchid', $reponse, false);
                $results = StdLib::getParam('results', $reponse, false);
                if ($results) {
                    $nbEnvois = count($results);
                    if ($nbEnvois == $nbDemandes) {
                        $FlashMessengerMessage = sprintf('%d SMS envoyé%s.', $nbEnvois,
                            $nbEnvois > 1 ? 's' : '');
                        $FlashMessengerType = FlashMessenger::NAMESPACE_SUCCESS;
                    } elseif ($nbEnvois < $nbDemandes) {
                        $FlashMessengerMessage = sprintf(
                            '%d SMS envoyé%s sur %d demandé%s', $nbEnvois,
                            $nbEnvois > 1 ? 's' : '', $nbDemandes,
                            $nbDemandes > 1 ? 's' : '');
                        $FlashMessengerType = FlashMessenger::NAMESPACE_WARNING;
                    } else {
                        $FlashMessengerMessage = sprintf(
                            'Attention ! %d SMS envoyé%s sur %d demandé%s', $nbEnvois,
                            $nbEnvois > 1 ? 's' : '', $nbDemandes,
                            $nbDemandes > 1 ? 's' : '');
                        $FlashMessengerType = FlashMessenger::NAMESPACE_ERROR;
                    }
                    $pb = false;
                    $tEsendexBatches = $this->db_manager->get(
                        'Sbm\Db\Table\EsendexBatches');
                    $oEsendex = $tEsendexBatches->getObjData();
                    try {
                        $oEsendex->exchangeArray(
                            [
                                'date' => $date_envoi,
                                'batchid' => $batchid,
                                'body' => $form->getData()['body'],
                                'nb_demandes' => $nbDemandes,
                                'nb_envois' => $nbEnvois
                            ]);
                        $tEsendexBatches->saveRecord($oEsendex);
                        $esendexbatchId = $tEsendexBatches->getTableGateway()->getLastInsertValue();
                        $tEsendexSms = $this->db_manager->get('Sbm\Db\Table\EsendexSms');
                        foreach ($results as $resultItem) {
                            try {
                                $oEsendex = $tEsendexSms->getObjData();
                                $oEsendex->exchangeArray(
                                    [
                                        'esendexbatchId' => $esendexbatchId,
                                        'id' => $resultItem->id(),
                                        'uri' => $resultItem->uri()
                                    ]);
                                $tEsendexSms->saveRecord($oEsendex);
                            } catch (\Exception $e) {
                                $this->api_sms->getLogger()->log(Logger::ERR,
                                    $e->getMessage(), $oEsendex->getArrayCopy());
                                $pb = true;
                            }
                        }
                        $tEsendexTelephones = $this->db_manager->get(
                            'Sbm\Db\Table\EsendexTelephones');
                        foreach ($arrayTo as $numero_tel => $destinataire) {
                            try {
                                $oEsendex = $tEsendexTelephones->getObjData();
                                $oEsendex->exchangeArray(
                                    [
                                        'esendexbatchId' => $esendexbatchId,
                                        'telephone' => $numero_tel,
                                        'destinataire' => $destinataire
                                    ]);
                                $tEsendexTelephones->saveRecord($oEsendex);
                            } catch (\Exception $e) {
                                $this->api_sms->getLogger()->log(Logger::ERR,
                                    $e->getMessage(), $oEsendex->getArrayCopy());
                                $pb = true;
                            }
                        }
                        if (! $pb) {
                            return $this->retour($FlashMessengerType,
                                $FlashMessengerMessage);
                        }
                    } catch (\Exception $e) {
                        $this->api_sms->getLogger()->log(Logger::ERR, $e->getMessage(),
                            $oEsendex->getArrayCopy());
                        $pb = true;
                    }
                    if ($pb) {
                        return $this->retour(FlashMessenger::NAMESPACE_ERROR,
                            'Envoi du SMS effectué mais non enregistré.' .
                            ' Pour consulter les réponses utiliser l\'interface de Esendex.');
                    }
                } else {
                    return $this->retour(FlashMessenger::NAMESPACE_WARNING,
                        'Échec de l\'envoi de SMS : raison inconnue');
                }
            } catch (\Exception $e) {
                $this->api_sms->getLogger()->log(Logger::ERR, $e->getMessage());
                return $this->retour(FlashMessenger::NAMESPACE_WARNING,
                    'Échec de l\'envoi de SMS : ' . $e->getMessage());
            }
        } else {
            $this->api_sms->getLogger()->log(Logger::NOTICE,
                json_encode($form->getMessages()));
            return $this->retour(FlashMessenger::NAMESPACE_WARNING,
                'Échec de l\'envoi des SMS : Données invalides.');
        }
    }

    public function listePdfAction()
    {
        return $this->retour(FlashMessenger::NAMESPACE_INFO,
            'Cette fonction n\'est pas encore développée.');
    }

    /**
     *
     * @param string $flashnamespace
     *            prend ses valeurs dans les constantes de FlashMessenger
     * @param string $flashmsg
     * @return \Zend\Http\Response
     */
    private function retour(string $flashnamespace, string $flashmsg)
    {
        $this->flashMessenger()->addMessage($flashmsg, $flashnamespace);
        Session::remove('aTo', 'servicesms\sendns');
        try {
            return $this->redirectToOrigin()->back();
        } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
    }
}