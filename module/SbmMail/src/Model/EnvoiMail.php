<?php
/**
 * Listener pour traiter une demande d'envoi de mail
 *
 * L'évènement 'sendMail' contient :
 * - name : sendMail
 * - target : null
 * - params : [
 * 'to' => [], // tableau des adresses des destinataires.
 * // Les adresses sont données sous la forme ['email' => string, 'name' => string]
 * 'cc' => [], // comme 'to' - optionnel
 * 'bcc' => [], // comme 'to' - optionnel
 * 'subject' => string, // sujet qui sera concaténé à
 * config['sbm']['mail']['message']['subject']
 * 'body' => [], // le corps du message et les fichiers joints sous la forme
 * // ['text' => string, 'html' => string, 'files' => [])
 * // où files est un tableau de pièces jointes (chemin complet dans le filename)
 * ]
 *
 * @project sbm
 * @package SbmMail/Model
 * @filesource EnvoiMail.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 juil. 2021
 * @version 2021-2.6.3
 */
namespace SbmMail\Model;

use SbmBase\Model\StdLib;
use Zend\Mail;
use Zend\Mime;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class EnvoiMail implements ListenerAggregateInterface
{

    /**
     *
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = [];

    /**
     * Tableau de configuration de Mail
     *
     * @var array
     */
    private $config;

    /**
     *
     * @var \SbmMail\Model\Dkim
     */
    private $dkim;

    /**
     * Tableau défini dans les module.config.php et sbm.local.php sous la clé 'sbm' =>
     * 'mail' => [] Ce tableau a pour clés 'transport', 'message' et 'destinataires'.
     * Les
     * destinaitaires sont les adresses de réception des messages adressés au service de
     * transport. (non utilisé ici)
     *
     * @param array $config_mail
     */
    public function __construct($config_mail, $dkim)
    {
        $this->config = $config_mail;
        $this->dkim = $dkim;
    }

    /**
     * Service manager
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    // protected $sm;

    /**
     *
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach('SbmMail\Send', 'sendMail',
            [
                $this,
                'onSendMail'
            ], 1);
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
     * Traitement de l'évènement 'sendMail'
     * Les paramètres de l'évènement (params) sont les données à enregistrer.
     *
     * @param Event $e
     *
     * @throws \SbmMail\Model\Exception
     */
    public function onSendMail(Event $e)
    {
        $params = $e->getParams();

        // message
        $mail = new Mail\Message();
        $mail->setEncoding($this->config['message']['message_encoding']);
        $mail->setFrom($this->config['message']['from']['email'],
            $this->config['message']['from']['name']);
        $mail->setReplyTo($this->config['message']['replyTo']['email'],
            $this->config['message']['replyTo']['name']);
        $to = empty($params['to']) ? [] : $params['to'];
        foreach ($to as $destinataire) {

            $mail->addTo($this->getAdresse($destinataire));
        }
        $cc = empty($params['cc']) ? [] : $params['cc'];
        foreach ($cc as $destinataire) {
            $mail->addCc($this->getAdresse($destinataire));
        }
        $bcc = empty($params['bcc']) ? [] : $params['bcc'];
        if (! empty($this->config['message']['bcc']['email'])) {
            $bcc[] = StdLib::getParamR([
                'message',
                'bcc'
            ], $this->config);
        }
        foreach ($bcc as $destinataire) {
            $mail->addBcc($this->getAdresse($destinataire));
        }
        $mail->setSubject($this->config['message']['subject'] . $params['subject']);
        $this->setBody($mail, $params['body'],
            $this->config['message']['message_encoding']);
        // transport
        if ($this->config['transport']['mode'] == 'sendmail') {
            $transport = new Mail\Transport\Sendmail();
        } elseif ($this->config['transport']['mode'] == 'smtp') {
            $options = new Mail\Transport\SmtpOptions();
            if (! empty($this->config['transport']['smtpOptions']['host'])) {
                $options->setHost($this->config['transport']['smtpOptions']['host']);
            }
            if (! empty($this->config['transport']['smtpOptions']['port'])) {
                $options->setPort($this->config['transport']['smtpOptions']['port']);
            }
            if (! empty($this->config['transport']['smtpOptions']['name'])) {
                $options->setName($this->config['transport']['smtpOptions']['name']);
            }
            if (! empty($this->config['transport']['smtpOptions']['connexion_class'])) {
                $options->setConnectionClass(
                    $this->config['transport']['smtpOptions']['connexion_class']);
            }
            if (! empty($this->config['transport']['smtpOptions']['connexion_config'])) {
                $options->setConnectionConfig(
                    $this->config['transport']['smtpOptions']['connexion_config']);
            }
            $transport = new Mail\Transport\Smtp($options);
        } else {
            throw new Exception('Le service d\'envoi de mail n\'est pas bien paramétré.');
        }
        $transport->send($this->dkim->sign($mail));
    }

    /**
     *
     * @param string|array $destinataire
     * @throws Exception
     * @return \Zend\Mail\Address
     */
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
     * Met en place dans $mail passé par référence le corps du message, avec attachments,
     * et le header (content-type).
     *
     * @param \Zend\Mail\Message $mail
     *            message passé par référence auquel il faut mettre un body avec
     *            éventuellement des attachments
     * @param array $bodyinfo
     *            Les clés de ce tableau sont 'text', 'html' et 'files'.<ul> <li>'text' =>
     *            Texte du message à envoyer sans formatage</li> <li>'html' => Texte du
     *            message à envoyer au format html</li> <li>'files' => array (tableau
     *            simple de noms de fichiers avec leur path)</li></ul>
     * @param string $encoding
     *
     * @throws \SbmMail\Model\Exception
     */
    protected function setBody(\Zend\Mail\Message &$mail, array $bodyinfo, $encoding)
    {
        if (! empty($bodyinfo['html'])) {
            $htmlPart = new Mime\Part($bodyinfo['html']);
            $htmlPart->setType(Mime\Mime::TYPE_HTML);
            $htmlPart->setCharset($encoding);
            if (empty($bodyinfo['text'])) {
                $bodyinfo['text'] = strip_tags(html_entity_decode($bodyinfo['html']));
            }
        } elseif (empty($bodyinfo['text'])) {
            throw new Exception(
                'Le message est vide. Donnez un texte, éventuellement formaté en html.');
        }
        if (! array_key_exists('files', $bodyinfo)) {
            $bodyinfo['files'] = [];
        }
        // on place obligatoirement un message en text
        $textPart = new Mime\Part($bodyinfo['text']);
        $textPart->setType(Mime\Mime::TYPE_TEXT);
        $textPart->setCharset($encoding);
        // et les fichiers joints
        $attaches = [];
        $fileinfo = finfo_open(FILEINFO_MIME_TYPE);
        if (! $fileinfo) {
            throw new Exception("Erreur à l'ouverture de fileinfo.");
        }
        foreach ($bodyinfo['files'] as $filename) {
            if (file_exists($filename)) {
                $filetype = finfo_file($fileinfo, $filename);
                $attachment = new Mime\Part(file_get_contents($filename));
                $attachment->setType("$filetype;name=$filename");
                $attachment->setEncoding(Mime\Mime::ENCODING_BASE64);
                $attachment->setDisposition(Mime\Mime::DISPOSITION_ATTACHMENT);
                $attachment->setFileName($filename); // juste le nom du fichier
                $attaches[] = $attachment;
            } else {
                throw new Exception("Impossible d'ouvrir le fichier $filename.");
            }
        }
        finfo_close($fileinfo);
        if (count($attaches) > 0) {
            $parts = array_merge([
                $textPart,
                $htmlPart
            ], $attaches);
            $type = Mime\Mime::MULTIPART_MIXED;
        } else {
            $parts = [
                $textPart,
                $htmlPart
            ];
            $type = Mime\Mime::MULTIPART_ALTERNATIVE;
        }
        $body = new Mime\Message();
        $body->setParts($parts);

        $mail->setBody($body)
            ->getHeaders()
            ->get('content-type')
            ->setType($type);
    }
}