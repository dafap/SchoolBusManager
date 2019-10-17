<?php
/**
 * Controller de l'API de Clever Sms Light
 *
 * @project sbm
 * @package SbmCleverSms/src/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 oct. 2019
 * @version 2019-2.5.2
 */
namespace SbmCleverSms\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Form;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\Http\PhpEnvironment\Response;
use Zend\Log\Logger;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $args = $this->initListe('cleversms');
        if ($args instanceof Response)
            return $args;

        return new ViewModel(
            [
                'paginator' => $this->db_manager->get('Sbm\Db\Table\CleverSms')->paginator(
                    $args['where'], [
                        'send_date Desc'
                    ]),
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_cleversms', 20),
                'criteres_form' => $args['form']
            ]);
    }

    public function accountInfoAction()
    {
        $result = $this->curl_request->curlInitialize('accounts/me', 'GET')->curlExec();
        return new ViewModel(
            [

                'account_info' => $result,
                'page' => $this->params('page', 1)
            ]);
    }

    /**
     * L'appel initial vient d'un POST contenant les paramètre 'url1_retour', 'telephones'
     * et 'responsable'
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
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
        if (empty($telephones) || array_key_exists('cancel', $args)) {
            return $this->retour(FlashMessenger::NAMESPACE_WARNING, 'Aucun SMS envoyé.');
        }
        $form = $this->form_manager->get(\SbmCleverSms\Form\Sms::class);
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $datas = json_encode(
                    [
                        'datas' => [
                            'text' => $form->getData()['body'],
                            'encoding' => 3,
                            'phoneNumbers' => $telephones
                        ]
                    ]);
                $response = $this->curl_request->curlInitialize('pushs', 'POST', $datas)->curlExec();
                if ($response->isValid()) {
                    try {
                        $tCleverSms = $this->db_manager->get('Sbm\Db\Table\CleverSms');
                        $oCleverSms = $tCleverSms->getObjData();
                        $oCleverSms->exchangeArray(
                            array_merge($response->getResponse('push'),
                                [
                                    'http_code' => $response->getCode()
                                ]));
                        $tCleverSms->saveRecord($oCleverSms);
                        return $this->retour(FlashMessenger::NAMESPACE_SUCCESS,
                            'Envoi du SMS effectué.');
                    } catch (\Exception $e) {
                        $this->curl_request->getLogger()->log(Logger::ERR,
                            $response->getMessage(), $telephones);
                        $this->curl_request->getLogger()->log(Logger::ERR,
                            $e->getMessage());
                        $this->curl_request->getLogger()->log(Logger::ERR,
                            $e->getTraceAsString());
                        return $this->retour(FlashMessenger::NAMESPACE_ERROR,
                            'Envoi du SMS effectué mais non enregistré.' .
                            ' Pour consulter les réponses utiliser l\'interface de CleverSms.');
                    }
                } else {
                    $this->curl_request->getLogger()->log(Logger::WARN,
                        $response->getMessage());
                    return $this->retour(FlashMessenger::NAMESPACE_WARNING,
                        'Échec de l\'envoi du SMS : ' . $response->getMessage());
                }
            }
        }
        $form->setData([
            'body' => StdLib::getParam('contenu', $args, '')
        ]);
        return new ViewModel(
            [
                'form' => $form,
                'destinataires' => (array) StdLib::getParam('responsable', $args, []),
                'telephones' => $telephones
            ]);
    }

    /**
     * Recherche un paramètre post parmi les suivants : circuitId, classeId, communeId,
     * etablissementId, lotId, organismeId, serviceId, stationId, tarifId, transporteurId
     * sinon, c'est une demande pour les responsables sélectionnés (selection == 1). Cette
     * méthode propose un formulaire de saisie mais ne traite pas la réponse afin de ne
     * pas compliquer l'analyse du post.
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function envoiGroupeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', [], 'cleversms/envoigroup');
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
            Session::set('post', $args, 'cleversms/envoigroup');
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
                    'serviceId' => $oCircuit->serviceId,
                    'stationId' => $oCircuit->stationId
                ]);
        }
        $qTelephones = $this->db_manager->get('Sbm\Db\Query\Responsable\Telephones');
        $resultset = $qTelephones->{$oTelephones->getQueryMethod()}(
            $oTelephones->getQueryParam());
        $aTo = [];
        foreach ($resultset as $row) {
            if ($row['telephoneF']) {
                $aTo[$row['telephoneF']] = $row['to'];
            }
            if ($row['telephoneP']) {
                $aTo[$row['telephoneP']] = $row['to'];
            }
            if ($row['telephoneT']) {
                $aTo[$row['telephoneT']] = $row['to'];
            }
        }
        if (empty($aTo)) {
            $message = 'Aucun numéro de téléphone trouvé. Pas d\'envoi.';
            return $this->retour(FlashMessenger::NAMESPACE_WARNING, $message);
        }
        Session::set('aTo', $aTo, 'cleversms/envoigroup');
        $form = $this->form_manager->get(\SbmCleverSms\Form\Sms::class);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmservicesms', [
                'action' => 'envoi-groupe-send'
            ]));
        $view = new ViewModel(
            [
                'form' => $form,
                'destinataires' => $aTo,
                'telephones' => array_keys($aTo)
            ]);
        return $view->setTemplate('sbm-clever-sms/index/envoi-sms.phtml');
    }

    public function envoiGroupeSendAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->retour(FlashMessenger::NAMESPACE_WARNING, 'Aucun SMS envoyé.');
        }
        $aTo = Session::get('aTo', [], 'cleversms/envoigroup');
        if (empty($aTo) || array_key_exists('cancel', $prg)) {
            return $this->retour(FlashMessenger::NAMESPACE_WARNING, 'Aucun SMS envoyé.');
        }
        $form = $this->form_manager->get(\SbmCleverSms\Form\Sms::class);
        $form->setData($prg);
        if ($form->isValid()) {
            $datas = json_encode(
                [
                    'datas' => [
                        'text' => $form->getData()['body'],
                        'encoding' => 3,
                        'phoneNumbers' => array_keys($aTo)
                    ]
                ]);
            $response = $this->curl_request->curlInitialize('pushs', 'POST', $datas)->curlExec();
            if ($response->isValid()) {
                try {
                    $tCleverSms = $this->db_manager->get('Sbm\Db\Table\CleverSms');
                    $oCleverSms = $tCleverSms->getObjData();
                    $oCleverSms->exchangeArray(
                        array_merge($response->getResponse('push'),
                            [
                                'http_code' => $response->getCode()
                            ]));
                    $tCleverSms->saveRecord($oCleverSms);
                    return $this->retour(FlashMessenger::NAMESPACE_SUCCESS,
                        'Envoi du SMS effectué.');
                } catch (\SbmCleverSms\Model\Exception\ExceptionInterface $e) {
                    $this->curl_request->getLogger()->log(Logger::ERR,
                        $response->getMessage(), array_keys($aTo));
                    $this->curl_request->getLogger()->log(Logger::ERR,
                        $response->debug_status());
                    $this->curl_request->getLogger()->log(Logger::ERR, $e->getMessage());
                    $this->curl_request->getLogger()->log(Logger::ERR,
                        $e->getTraceAsString());
                    return $this->retour(FlashMessenger::NAMESPACE_ERROR,
                        'Échec de l\'envoi des SMS.');
                } catch (\Exception $e) {
                    $this->curl_request->getLogger()->log(Logger::WARN, $e->getMessage(),
                        array_keys($aTo));
                    $this->curl_request->getLogger()->log(Logger::WARN,
                        $e->getTraceAsString());
                    return $this->retour(FlashMessenger::NAMESPACE_WARNING,
                        'Envoi du SMS effectué mais non enregistré.' .
                        ' Pour consulter les réponses utiliser l\'interface de CleverSms.');
                }
            } else {
                $response->getLogger()->log(Logger::CRIT, $response->getMessage());
                return $this->retour(FlashMessenger::NAMESPACE_ERROR,
                    'Échec de l\'envoi des SMS : ' . $response->getMessage());
            }
        }
        return $this->retour(FlashMessenger::NAMESPACE_INFO, 'Échec de l\'envoi des SMS : Données invalides.');
    }

    public function supprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new Form\ButtonForm([
            'id' => null
        ],
            [
                'supproui' => [
                    'class' => 'confirm default',
                    'value' => 'Confirmer'
                ],
                'supprnon' => [
                    'class' => 'confirm default',
                    'value' => 'Abandonner'
                ]
            ]);
        $params = [
            'data' => [
                'alias' => 'Sbm\Db\Table\CleverSms',
                'id' => 'cleversmsId'
            ],
            'form' => $form
        ];

        $r = $this->supprData($this->db_manager, $params,
            function ($id, $table) {
                return [
                    'id' => $id,
                    'data' => $table->getRecord($id)
                ];
            });

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
                    $data = StdLib::getParam('data', $r->getResult());
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'data' => $data,
                            'cleversmsId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    /**
     * EN ATTENTE - uniquement pour les envois différés.
     */
    public function envoiInfoAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            if (array_key_exists('origine', $args)) {
                $this->redirectToOrigin()->setBack($args['origine']);
                unset($args['origine']);
                Session::set('post', $args, $this->getSessionNamespace());
            }
        }
        $referencePush = StdLib::getParam('reference', $args, false);
        if (! $referencePush) {
            return $this->retour(FlashMessenger::NAMESPACE_WARNING,
                'Envoi de référence inconnue.');
        }
        $result = $this->curl_request->curlInitialize("push/$referencePush", 'GET')->curlExec();
        return new ViewModel(
            [

                'envoi_info' => $result,
                'page' => $this->params('page', 1)
            ]);
    }

    public function bilanAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            if (array_key_exists('origine', $args)) {
                $this->redirectToOrigin()->setBack($args['origine']);
                unset($args['origine']);
                Session::set('post', $args, $this->getSessionNamespace());
            }
        }
        if (array_key_exists('retour', $args)) {
            return $this->retour(FlashMessenger::NAMESPACE_INFO,
                'Vous venez de voir le bilan d\'une campagne d\'envoi de SMS');
        }
        $referencePush = StdLib::getParam('reference', $args, false);
        if (! $referencePush) {
            return $this->retour(FlashMessenger::NAMESPACE_WARNING,
                'Envoi de référence inconnue.');
        }
        $result = $this->curl_request->curlInitialize("pushs/$referencePush/deliveries",
            'GET')->curlExec();
        return new ViewModel([

            'bilan' => $result,
            'page' => $this->params('page', 1)
        ]);
    }

    public function mosAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            if (array_key_exists('origine', $args)) {
                $this->redirectToOrigin()->setBack($args['origine']);
                unset($args['origine']);
                Session::set('post', $args, $this->getSessionNamespace());
            }
        }
        if (array_key_exists('retour', $args)) {
            return $this->retour(FlashMessenger::NAMESPACE_INFO,
                'Vous venez de voir les réponses reçues suite à une campagne d\'envoi de SMS');
        }
        $referencePush = StdLib::getParam('reference', $args, false);
        if (! $referencePush) {
            return $this->retour(FlashMessenger::NAMESPACE_WARNING,
                'Envoi de référence inconnue.');
        }
        $result = $this->curl_request->curlInitialize("pushs/$referencePush/mos", 'GET')->curlExec();
        return new ViewModel([

            'mos' => $result,
            'page' => $this->params('page', 1)
        ]);
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
        // echo '<pre>';
        // print_r($this->redirectToOrigin()->back());
        // die();
        try {
            return $this->redirectToOrigin()->back();
        } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
    }
}