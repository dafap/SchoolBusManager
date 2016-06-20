<?php
/**
 * Actions communes pour l'envoi de fichier
 *
 * Les adresses de destination doivent être configurées dans le fichier config/autolaod/sbm.local.php
 * (voir $mail, clé 'destinataires')
 * Les adresses 'from' et 'replyTo' se trouvent aussi dans ce fichier de configuration (clé 'message')
 * 
 * @project sbm
 * @package DafapMail/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 juin 2016
 * @version 2016-2.1.6
 */
namespace DafapMail\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use DafapMail\Model\Template as MailTemplate;
use DafapSession\Model\Session;
use SbmCommun\Model\StdLib;

class IndexController extends AbstractActionController
{

    /**
     * Par défaut, page d'envoi d'un message au service de transport.
     * (à configurer dans config/autolaod/sbm.local.php)
     *
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = (array) $prg;
        if (array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addWarningMessage('Aucun message envoyé.');
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                return $this->redirect()->toRoute('login', array(
                    'action' => 'home-page'
                ));
            }
        }
        $user = $this->config['user'];
        $form = $this->config['form_manager']->get('Dafap\MailForm');
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $data = $form->getData();
                // préparation du corps
                $body = "<p><b>Message envoyé par %s %s %s depuis School bus manager<br>Email: %s</b></p><hr>";
                $body = sprintf($body, $user['titre'], $user['nom'], $user['prenom'], $user['email']);
                if ($data['body'] == strip_tags($data['body'])) {
                    // c'est du txt
                    $body .= nl2br($data['body']);
                } else {
                    // c'est du html
                    $body .= $data['body'];
                }
                // envoie l'email
                $params = array(
                    'bcc' => StdLib::getParam('destinataires', $this->config['mail_config']),
                    'cc' => array(
                        array(
                            'email' => $user['email'],
                            'name' => $user['nom'] . ' ' . $user['prenom']
                        )
                    ),
                    'subject' => $data['subject'],
                    'body' => array(
                        'html' => $body
                    )
                );
                $this->getEventManager()->addIdentifiers('SbmMail\Send');
                $this->getEventManager()->trigger('sendMail', null, $params);
                $this->flashMessenger()->addInfoMessage('Le message a été envoyé au service de transport et une copie vous a été adressée. Consultez votre messagerie.');
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                    return $this->redirect()->toRoute('login', array(
                        'action' => 'home-page'
                    ));
                }
            }
        }
        $form->setData(array(
            'userId' => $user['userId']
        ));
        return new ViewModel(array(
            'form' => $form->prepare(),
            'user' => $user
        ));
    }

    /**
     * Envoie des mails aux transporteurs lorsque des changements ont lieu dans les
     * inscriptions des enfants qu'ils transportent (nouvelle affectation, changement
     * d'affectation, suppression d'une affectation ou élève rayé)
     * Cette tâche doit être planifiée dans un cron.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function lastDayChangesAction()
    {
        $tCalendar = $this->config['db_manager']->get('Sbm\Db\System\Calendar');
        try {
            $millesime = $tCalendar->getCurrentMillesime();
            $history = $this->config['db_manager']->get('Sbm\Db\Query\History');
            $services = $this->config['db_manager']->get('Sbm\Db\Table\Services');
            $transporteurs = $this->config['db_manager']->get('Sbm\Db\Table\Transporteurs');
            $destinataires = array();
            $changes = $history->getLastDayChanges('affectations', $millesime);
            if ($changes instanceof \Traversable) {
                foreach ($changes as $affectation) {
                    $log = explode('|', $affectation['log']);
                    if (count($log) >= 4) {
                        $oservice = $services->getRecord($log[3]);
                        // enregistrement sans doublon
                        $destinataires[$oservice->transporteurId][$oservice->serviceId] = $oservice->serviceId;
                    }
                    if (count($log) == 6) {
                        $oservice = $services->getRecord($log[5]);
                        // enregistrement sans doublon
                        $destinataires[$oservice->transporteurId][$oservice->serviceId] = $oservice->serviceId;
                    }
                }
                $logo_bas_de_mail = 'bas-de-mail-service-gestion.png';
                $mailTemplate = new MailTemplate('avertissement-transporteur', 'layout', [
                    'file_name' => $logo_bas_de_mail,
                    'path' => StdLib::getParamR([
                        'img',
                        'path'
                    ], $this->config),
                    'img_attributes' => StdLib::getParamR([
                        'img',
                        'administrer',
                        $logo_bas_de_mail
                    ], $this->config),
                    'client' => StdLib::getParam('client', $this->config)
                ]);
                $qtransporteurs = $this->config['db_manager']->get('Sbm\Db\Query\Transporteurs');
                $controle = array();
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
                    
                    $params = array(
                        'to' => array_values($to),
                        'subject' => 'Modification des inscriptions',
                        'body' => array(
                            'html' => $mailTemplate->render(array(
                                'services' => $circuits,
                                'url_portail' => $this->url()
                                    ->fromRoute('sbmportail', array(
                                    'action' => 'tr-index'
                                ), array(
                                    'force_canonical' => true
                                ))
                            ))
                        )
                    );
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
        } catch (\SbmCommun\Model\Db\Exception $e) {
            $message = 'Le service d\'alerte des transporteurs est interrompu durant les vacances. Les envois reprendront à partir du début de l\'année scolaire.';
        }
        
        return $this->getResponse()
            ->setContent($message)
            ->setStatusCode(200);
    }
}