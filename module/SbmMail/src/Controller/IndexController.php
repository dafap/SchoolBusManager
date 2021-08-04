<?php
/**
 * Actions communes pour l'envoi de fichier
 *
 * Les adresses de destination doivent être configurées dans le fichier
 * config/autolaod/sbm.local.php
 * (voir $mail, clé 'destinataires')
 * Les adresses 'from' et 'replyTo' se trouvent aussi dans ce fichier de configuration
 * (clé 'message')
 *
 * @project sbm
 * @package SbmMail/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2021
 * @version 2021-2.5.14
 */
namespace SbmMail\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmMail\Form;
use SbmMail\Model\Template as MailTemplate;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\View\Model\ViewModel;

/**
 *
 * @property \SbmInstallation\Model\Theme $theme
 * @property \SbmCommun\Model\Db\Service\DbManager $db_manager
 * @property \SbmCommun\Model\Service\FormManager $form_manager
 * @property array $user
 * @property array $mail_config
 * @property array $img
 * @property array $client
 *
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 *
 */
class IndexController extends AbstractActionController
{

    /**
     * Par défaut, page d'envoi d'un message au service de transport.
     * (à configurer dans
     * config/autolaod/sbm.local.php) (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addWarningMessage('Aucun message envoyé.');
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                return $this->redirect()->toRoute('login', [
                    'action' => 'home-page'
                ]);
            }
        }
        $user = $this->user;
        $form = $this->form_manager->get(Form\Mail::class);
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $data = $form->getData();
                // préparation du corps
                $body = "<p><b>Message envoyé par %s %s %s depuis School bus manager<br>Email: %s</b></p><hr>";
                $body = sprintf($body, $user['titre'], $user['nom'], $user['prenom'],
                    $user['email']);
                if ($data['body'] == strip_tags($data['body'])) {
                    // c'est du txt
                    $body .= nl2br($data['body']);
                } else {
                    // c'est du html
                    $body .= $data['body'];
                }
                // envoie l'email
                $params = [
                    'to' => StdLib::getParam('destinataires', $this->mail_config),
                    'cc' => [
                        [
                            'email' => $user['email'],
                            'name' => $user['nom'] . ' ' . $user['prenom']
                        ]
                    ],
                    'subject' => $data['subject'],
                    'body' => [
                        'html' => $body
                    ]
                ];
                $this->getEventManager()->addIdentifiers('SbmMail\Send');
                $this->getEventManager()->trigger('sendMail', null, $params);
                $this->flashMessenger()->addInfoMessage(
                    'Le message a été envoyé au service de transport et une copie vous a été adressée. Consultez votre messagerie.');
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                    return $this->redirect()->toRoute('login', [
                        'action' => 'home-page'
                    ]);
                }
            }
        }
        $form->setData([
            'userId' => $user['userId']
        ]);
        return new ViewModel(
            [
                'theme' => $this->theme,
                'client' => $this->client,
                'form' => $form->prepare(),
                'user' => $user
            ]);
    }

    /**
     * Envoie des mails aux transporteurs lorsque des changements ont lieu dans les
     * inscriptions des enfants qu'ils transportent (nouvelle affectation, changement
     * d'affectation, suppression d'une affectation ou élève rayé) Cette tâche doit être
     * planifiée dans un cron.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function lastDayChangesAction()
    {
        $tCalendar = $this->db_manager->get('Sbm\Db\System\Calendar');
        try {
            $millesime = $tCalendar->getCurrentMillesime();
            $history = $this->db_manager->get('Sbm\Db\Query\History');
            $services = $this->db_manager->get('Sbm\Db\Table\Services');
            $transporteurs = $this->db_manager->get('Sbm\Db\Table\Transporteurs');
            $destinataires = [];
            $changes = $history->getLastDayChanges('affectations', $millesime);
            if ($changes instanceof \Traversable) {
                foreach ($changes as $affectation) {
                    $log = explode('|', $affectation['log']);
                    if (count($log) >= 4) {
                        $oservice = $services->getRecord($log[3]);
                        // enregistrement sans doublon
                        $destinataires[$oservice->transporteurId][$oservice->lotId] = $oservice->lotId;
                    }
                    if (count($log) == 6) {
                        $oservice = $services->getRecord($log[5]);
                        // enregistrement sans doublon
                        $destinataires[$oservice->transporteurId][$oservice->lotId] = $oservice->lotId;
                    }
                }
                $logo_bas_de_mail = 'bas-de-mail-service-gestion.png';
                $mailTemplate = new MailTemplate('avertissement-transporteur', 'layout',
                    [
                        'file_name' => $logo_bas_de_mail,
                        'path' => StdLib::getParam('path', $this->img),
                        'img_attributes' => StdLib::getParamR(
                            [
                                'administrer',
                                $logo_bas_de_mail
                            ], $this->img),
                        'client' => $this->client
                    ]);
                $qtransporteurs = $this->db_manager->get('Sbm\Db\Query\Transporteurs');
                $controle = [];
                foreach ($destinataires as $transporteurId => $circuits) {
                    $odata = $transporteurs->getRecord($transporteurId);
                    $email = $odata->email;
                    $controle[] = $odata->nom;
                    $to = [];
                    if (! empty($email)) {
                        $to[$email] = [
                            'email' => $email,
                            'name' => $odata->nom
                        ];
                    }
                    $users = $qtransporteurs->getUserEmails($transporteurId);
                    foreach ($users as $user) {
                        $to[$user['email']] = [
                            'email' => $user['email'],
                            'name' => $user['nomprenom']
                        ];
                    }

                    if (empty($to))
                        continue;

                    $params = [
                        'to' => array_values($to),
                        'subject' => 'Modification des inscriptions',
                        'body' => [
                            'html' => $mailTemplate->render(
                                [
                                    'services' => $circuits,
                                    'url_portail' => $this->url()
                                        ->fromRoute('sbmportail',
                                        [
                                            'action' => 'tr-index'
                                        ], [
                                            'force_canonical' => true
                                        ])
                                ])
                        ]
                    ];
                    $this->getEventManager()->addIdentifiers('SbmMail\Send');
                    $this->getEventManager()->trigger('sendMail', null, $params);
                }
            }
            if (empty($controle)) {
                $message = 'Durant les dernières 24 heures, il n\'y a pas eu de modification d\'inscription. Par conséquent, aucun mail n\'a été envoyé.';
            } else {
                $message = 'Suite aux modifications d\'inscription qui ont eu lieu durant les dernières 24 heures les transporteurs suivants ont reçu un email d\'information.';
                foreach ($controle as $value) {
                    $message .= "\n - $value";
                }
            }
        } catch (\SbmCommun\Model\Db\Exception\ExceptionInterface $e) {
            $message = 'Le service d\'alerte des transporteurs est interrompu durant les vacances. Les envois reprendront à partir du début de l\'année scolaire.';
        }

        return $this->getResponse()
            ->setContent($message)
            ->setStatusCode(200);
    }

    /**
     * Recherche un paramètre post parmi les suivants : circuitId, classeId, communeId,
     * etablissementId, lotId, organismeId, serviceId, stationId, tarifId, transporteurId
     * sinon, c'est une demande pour les responsables sélectionnés (selection == 1).
     * Cette
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
            $args = Session::get('post', [], 'sbmmail/envoigroup');
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
            Session::set('post', $args, 'sbmmail/envoigroup');
            $initRedirectToOrigin = true;
        }
        $oEmails = new \SbmGestion\Model\Communication\Emails($args);
        if ($initRedirectToOrigin) {
            $this->redirectToOrigin()->setBack($oEmails->getUrlBack());
        }
        if ($oEmails->getFilterName() == 'circuit') {
            $oCircuit = $this->db_manager->get('Sbm\Db\Table\Circuits')->getRecord(
                $oEmails->getFilterValue());
            $oEmails->setQueryParams(
                [
                    'serviceId' => $oCircuit->serviceId,
                    'stationId' => $oCircuit->stationId
                ]);
        }
        $qEmails = $this->db_manager->get('Sbm\Db\Query\Responsable\Emails');
        $resultset = $qEmails->{$oEmails->getQueryMethod()}($oEmails->getQueryParam());
        $aTo = [];
        foreach ($resultset as $row) {
            $aTo[$row['email']] = $row['to'];
        }
        if (empty($aTo)) {
            $message = 'Aucun email trouvé. Pas d\'envoi.';
            return $this->retour(FlashMessenger::NAMESPACE_WARNING, $message);
        }
        Session::set('aTo', $aTo, 'sbmmail/envoigroup');
        $form = $this->form_manager->get(Form\Mail::class);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('SbmMail', [
                'action' => 'envoi-groupe-send'
            ]));
        $view = new ViewModel(
            [
                'form' => $form,
                'destinataires' => $aTo,
                'emails' => array_keys($aTo)
            ]);
        return $view->setTemplate('sbm-mail/index/index.phtml');
    }

    public function envoiGroupeSendAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->retour(FlashMessenger::NAMESPACE_WARNING, 'Aucun SMS envoyé.');
        }
        $aTo = Session::get('aTo', [], 'sbmmail/envoigroup');
        if (empty($aTo) || array_key_exists('cancel', $prg)) {
            return $this->retour(FlashMessenger::NAMESPACE_WARNING, 'Aucun SMS envoyé.');
        }
        $destinataires = [];
        foreach ($aTo as $key => $value) {
            $destinataires[] = [
                'email' => $key,
                'name' => $value
            ];
        }
        $form = $this->form_manager->get(Form\Mail::class);
        $form->setData($prg);
        if ($form->isValid()) {
            $data = $form->getData();
            // préparation du corps
            if ($data['body'] == strip_tags($data['body'])) {
                // c'est du txt
                $body = nl2br($data['body']);
            } else {
                // c'est du html
                $body = $data['body'];
            }
            // préparation des paramètres d'envoi
            $auth = $this->authenticate->by();
            $user = $auth->getIdentity();
            $logo_bas_de_mail = 'bas-de-mail-service-gestion.png';
            $mailTemplate = new MailTemplate(null, 'layout',
                [
                    'file_name' => $logo_bas_de_mail,
                    'path' => StdLib::getParam('path', $this->img),
                    'img_attributes' => StdLib::getParamR(
                        [
                            'administrer',
                            $logo_bas_de_mail
                        ], $this->img),
                    'client' => $this->client
                ]);

            $params = [
                'bcc' => $destinataires,
                'to' => [
                    [
                        'email' => $user['email'],
                        'name' => 'School bus manager'
                    ]
                ],
                'subject' => $data['subject'],
                'body' => [
                    'html' => $mailTemplate->render([

                        'body' => $body
                    ])
                ]
            ];
            // envoi du mail
            $this->getEventManager()->addIdentifiers('SbmMail\Send');
            $this->getEventManager()->trigger('sendMail', null, $params);
            return $this->retour(FlashMessenger::NAMESPACE_SUCCESS,
                'Le message a été envoyé et une copie vous est adressée dans votre messagerie.');
        }
        return $this->retour(FlashMessenger::NAMESPACE_INFO, 'Données invalides.');
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
        try {
            return $this->redirectToOrigin()->back();
        } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home-page'
            ]);
        }
    }
}