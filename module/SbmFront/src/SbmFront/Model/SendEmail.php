<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource SendEmail.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 févr. 2015
 * @version 2015-1
 */
namespace SbmFront\Model;

use Zend\Di\ServiceLocator;
use SbmCommun\Model\StdLib;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Message;
class SendEmail
{
    private $config;
    private $sm;
    private $transport;
    
    public function __construct(ServiceLocator $sm)
    {
        $this->sm = $sm;
        $config = $sm->get('Config');
        $this->config = StdLib::getParamR(array('sbm', 'email'), $config);
        $this->transport = new Smtp();
        $this->transport->setOptions(new SmtpOptions(StdLib::getParamR(array('transport', 'option'), $this->config)));
    }
    
    /**
     * Envoie un email à l'utilisateur @auth en lui demandant de se connecter à l'adresse $url
     * 
     * @param object $auth
     * @param string $url
     */
    public function sendConfirmationEmail($auth, $url)
    {
        // @toto: Construire le corps du message
        $body = 'à construire ...';
        $message = new Message();
        $message->addTo($auth->usr_email)
        ->addFrom($this->config['from'])
        ->setSubject('[Transports scolaires] Confirmer la demande de création du compte')
        ->setBody($body);
        $this->transport->send($message);
    }
} 