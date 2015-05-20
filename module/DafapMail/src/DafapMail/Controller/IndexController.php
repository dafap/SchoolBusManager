<?php
/**
 * Actions communes pour l'envoi de fichier
 *
 * Les adresses de destination doivent être configurées dans le fichier config/autolaod/sbm.local.php
 * (voir $mail, clé 'destinataires')
 * Les adresses 'from' et 'replyTo' se trouvent aussi dans ce fichier de configuration (clé 'message')
 * 
 * @project project_name
 * @package package_name
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 mai 2015
 * @version 2015-1
 */
namespace DafapMail\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use DafapMail\Form\Mail as MailForm;
use DafapMail\Model\Template;
use SbmCommun\Model\StdLib;

class IndexController extends AbstractActionController
{
    /**
     * Par défaut, page d'envoi d'un message au service de transport.
     * (à configurer dans config/autolaod/sbm.local.php)
     * 
     * (non-PHPdoc)
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $auth = $this->getServiceLocator()
        ->get('Dafap\Authenticate')
        ->by();
        
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = (array) $prg;
        if (array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addWarningMessage('Aucun message envoyé.');
            try {
                return $this->redirectToOrigin()->back();
            } catch (\Exception $e) {
                return $this->redirect()->toRoute('login', array('action' => 'home-page'));
            }
        }
        $user = $auth->getIdentity();
        $form = new MailForm();
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
                    'bcc' => StdLib::getParamR(array('sbm', 'mail', 'destinataires'),$this->getServiceLocator()->get('config')),
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
                $this->getEventManager()->trigger('sendMail', $this->getServiceLocator(), $params);
                $this->flashMessenger()->addInfoMessage('Un mail a été envoyé à l\'adresse indiquée. Consultez votre messagerie.');
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\Exception $e) {
                    return $this->redirect()->toRoute('login', array('action' => 'home-page'));
                }
            }
        }
        $form->setData(array('userId' => $user['userId']));
        return new ViewModel(array(
            'form' => $form->prepare(),
            'user' => $user
        ));
    }
}