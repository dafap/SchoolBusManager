<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 *
 * @project sbm
 * @package
 * @filesource ApiSms.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model;

use Esendex;

class ApiSms implements ApiSmsInterface
{
    use \SbmCommun\Model\Log\LoggerTrait;

    /**
     *
     * @var Esendex\Authentication\AbstractAuthentication
     */
    private $authentication;

    /**
     *
     * @var string
     */
    private $api_url;

    /**
     *
     * @var string
     */
    private $originatorId;

    public function __construct(
        Esendex\Authentication\AbstractAuthentication $authentication, string $api_url,
        string $path_filelog, string $filename, string $originatorId)
    {
        $this->authentication = $authentication;
        $this->api_url = $api_url;
        $this->originatorId = $originatorId;
        $this->setFileLog($path_filelog, $filename); // utiliser le logger par
                                                     // $this->getLogger()->log()
    }

    /**
     *
     * @param array $recipients
     * @param string $body
     * @return array tableau de la forme ['batchid'=>string, 'results'=>
     *         Esendex\Model\ResultItem[]]
     */
    public function envoyer(array $recipients, string $body)
    {
        $messages = new DispatchArrayMessage($body);
        $messages->setOriginator($this->originatorId)->setRecipients($recipients);
        return (new DispatchService($this->authentication))->send($messages);
    }

    /**
     * Renvoie le crédit disponible
     *
     * @return number
     */
    public function getCredits()
    {
        return (new DispatchService($this->authentication))->getCredits();
    }

    /**
     * Renvoie les paramètres du compte ainsi que le crédit disponible
     *
     * @return \Esendex\Model\Account[]
     */
    public function getAccounts()
    {
        return (new Esendex\AccountService($this->authentication))->getAccounts();
    }

    /**
     * Renvoie le crédit consommé pour l'envoi du m$message à 1 destinataire
     *
     * @param string $message
     * @return \Esendex\Model\MessageInformation
     */
    public function getCout(string $message)
    {
        return (new Esendex\MessageInformationService($this->authentication))->getInformation(
            $message, Esendex\Model\MessageBody::CharsetAuto)->parts();
    }

    /**
     *
     * @param string $batchid
     * @return NULL[]|string[]|\SbmEsendex\Model\MessagesBatch[]
     */
    public function getMessagesBatch(string $batchid)
    {
        return (new MessagesBatchService($this->authentication))->getMessagesBatch(
            $batchid);
    }

    /**
     * Renvoie la liste des messages adressés à ce destinataire
     *
     * @param string $telephone
     */
    public function getMessagesHeader(string $telephone)
    {
        // vérifie que telephone commence par 0
        $p = strpos($telephone, '0');
        if ($p === false || $p > 0) {
            throw new TelephoneException(
                'Ce numéro de téléphone n\'a pas le format d\'un numéro de mobile français.');
        }
        $international = '33' . substr($telephone, 1);
        return (new MessageHeaderService($this->authentication))->query(
            "to=$international");
    }

    public function getMessageBody(string $bodyUri)
    {
        return (new Esendex\MessageBodyService($this->authentication))->getMessageBody($bodyUri);
    }
}