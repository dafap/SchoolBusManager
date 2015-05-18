<?php
/**
 * Listener pour traiter une demande d'envoi de mail
 *
 * L'évènement 'sendMail' contient :
 * - name : sendMail
 * - target : ServiceManager
 * - params : array(
 *              'to' => array(),     // tableau des adresses des destinataires. 
 *                                      Les adresses sont données sous la forme array('email' => string, 'name' => string)
 *              'cc' => array(),     // optionnel - comme 'to'
 *              'bcc' => array(),    // optionnel - comme 'to'                        
 *              'subject' => string, // sujet qui sera concaténé à config['sbm']['mail']['message']['subject']
 *              'body' => array(),   // le corps du message et les fichiers joints sous la forme array('text' => string, 'html' => string, 'files' => array())
 *                                   // où files est un tableau de filename (avec chemin complet dans le filename)
 *              '' => array()        // les pièces jointes
 *           )
 *           
 * @project sbm
 * @package DafapMail/Model
 * @filesource EnvoiMail.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 mai 2015
 * @version 2015-1
 */
namespace DafapMail\Model;

use Zend\Mail;
use Zend\Mime;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\Event;
use Zend\ServiceManager\ServiceLocatorInterface;

class EnvoiMail implements ListenerAggregateInterface
{

    /**
     *
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Service manager
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $sm;

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach('SbmMail\Send', 'sendMail', array(
            $this,
            'onSendMail'
        ), 1);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\EventManager\ListenerAggregateInterface::detach()
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Traitement de l'évènement 'paiementOK'
     * Le contexte de l'évènement est le ServiceManager.
     * Les paramètres sont les données à enregistrer.
     *
     * @param Event $e            
     */
    public function onSendMail(Event $e)
    {
        $this->setServiceLocator($e->getTarget());
        $params = $e->getParams();
        $config = $this->sm->get('config')['sbm']['mail'];
        
        // message
        $mail = new Mail\Message();
        $mail->setEncoding($config['message']['message_encoding']);
        $mail->setFrom($config['message']['from']['email'], $config['message']['from']['name']);
        $mail->setReplyTo($config['message']['replyTo']['email'], $config['message']['replyTo']['name']);
        $to = empty($params['to']) ? array() : $params['to'];
        foreach ($to as $destinataire) {
            
            $mail->addTo($this->getAdresse($destinataire));
        }
        $cc = empty($params['cc']) ? array() : $params['cc'];
        foreach ($cc as $destinataire) {
            $mail->addCc($this->getAdresse($destinataire));
        }
        $bcc = empty($params['bcc']) ? array() : $params['bcc'];
        foreach ($bcc as $destinataire) {
            $mail->addBcc($this->getAdresse($destinataire));
        }
        $mail->setSubject($config['message']['subject'] . $params['subject']);
        $this->setBody($mail, $params['body'], $config['message']['message_encoding']);
        // transport
        if ($config['transport']['mode'] == 'sendmail') {
            $transport = new Mail\Transport\Sendmail();
        } elseif ($config['transport']['mode'] == 'smtp') {
            $options = new Mail\Transport\SmtpOptions();
            if (! empty($config['transport']['smtpOptions']['host'])) {
                $options->setHost($config['transport']['smtpOptions']['host']);
            }
            if (! empty($config['transport']['smtpOptions']['port'])) {
                $options->setPort($config['transport']['smtpOptions']['port']);
            }
            if (! empty($config['transport']['smtpOptions']['name'])) {
                $options->setName($config['transport']['smtpOptions']['name']);
            }
            if (! empty($config['transport']['smtpOptions']['connexion_class'])) {
                //$options->setConnectionClass($config['transport']['smtpOptions']['connexion_class']);
            }
            if (! empty($config['transport']['smtpOptions']['connexion_config'])) {
                //$options->setConnectionConfig($config['transport']['smtpOptions']['connexion_config']);
            }
            $transport = new Mail\Transport\Smtp($options);
        } else {
            throw new Exception('Le service d\'envoi de mail n\'est pas bien paramétré.');
        }
        $transport->send($mail);
    }

    protected function getAdresse($destinataire)
    {
        if (is_string($destinataire)) {
            return new Mail\Address($destinataire);
        } elseif (is_array($destinataire)) {
            if (empty($destinataire['email'])) {
                throw new Exception('Une adresse donnée est invalide.');
            }
            $email = $destinataire['email'];
            $name = empty($destinataire['name']) ? null : $destinataire['name'];
            return new Mail\Address($email, $name);
        }
    }

    /**
     * Met en place dans $mail passé par référence le corps du message, avec attachments, et le header (content-type).
     *
     * @param \Zend\Mail\Message $mail
     *            message passé par référence auquel il faut mettre un body avec éventuellement des attachments
     * @param array $bodyinfo
     *            Les clés de ce tableau sont 'text', 'html' et 'files'.<ul>
     *            <li>'text' => Texte du message à envoyer sans formatage</li>
     *            <li>'html' => Texte du message à envoyer au format html</li>
     *            <li>'files' => array (tableau simple de noms de fichiers avec leur path)</li></ul>
     * @throws \DafapMail\Model\Exception
     */
    protected function setBody(\Zend\Mail\Message &$mail, array $bodyinfo, $encoding)
    {
        if (! empty($bodyinfo['html'])) {
            $content = new Mime\Message();
            $htmlPart = new Mime\Part($bodyinfo['html']);
            $htmlPart->type = Mime\Mime::TYPE_HTML;
            $htmlPart->charset = $encoding;
            if (empty($bodyinfo['text'])) {
                $bodyinfo['text'] = strip_tags($bodyinfo['html']);
            }
        } elseif (empty($bodyinfo['text'])) {
            throw new Exception('Le message est vide. Donnez un texte, éventuellement formaté en html.');
        }
        if (!array_key_exists('files', $bodyinfo)) {
            $bodyinfo['files'] = array();
        }
        // on place obligatoirement un message en text
        $content = new Mime\Message();
        $textPart = new Mime\Part($bodyinfo['text']);
        $textPart->type = Mime\Mime::TYPE_TEXT;
        // et les fichiers joints
        $attaches = array();
        $fileinfo = finfo_open(FILEINFO_MIME_TYPE);
        if (! $fileinfo) {
            throw new Exception("Erreur à l'ouverture de fileinfo.");
        }
        foreach ($bodyinfo['files'] as $filename) {
            if (file_exists($filename)) {
                $filetype = finfo_file($fileinfo, $filename);
                $attachment = new Mime\Part(file_get_contents($filename));
                $attachment->type = "$filetype;name=$filename";
                $attachment->encoding = Mime\Mime::ENCODING_BASE64;
                $attachment->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;
                $attachment->filename = $filename; // juste le nom du fichier
                $attaches[] = $attachment;
            } else {
                throw new Exception("Impossible d'ouvrir le fichier $filename.");
            }
        }
        finfo_close($fileinfo);
        if (count($attaches) > 0) {
            $parts = array_merge(array(
                $textPart,
                $htmlPart
            ), $attaches);
            $type = Mime\Mime::MULTIPART_MIXED;
        } else {
            $parts = array(
                $textPart,
                $htmlPart
            );
            $type = Mime\Mime::MULTIPART_ALTERNATIVE;
        }
        $body = new Mime\Message();
        $body->setParts($parts);
        
        $mail->setBody($body)
            ->getHeaders()
            ->get('content-type')
            ->setType($type);
    }

    /**
     * Installe le service manager
     *
     * @param ServiceLocatorInterface $sm            
     */
    protected function setServiceLocator(ServiceLocatorInterface $sm)
    {
        $this->sm = $sm;
    }

    /**
     * Renvoie le service manager
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected function getServiceLocator()
    {
        return $this->sm;
    }
} 