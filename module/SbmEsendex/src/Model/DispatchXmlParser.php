<?php
/**
 * Cette classe surcharge la classe du Sdk Esendex pour traiter l'envoi d'un groupe de messages.
 *
 *
 * @project sbm
 * @package SbmEnsendex/src/Model
 * @filesource DispatchXmlParser.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model;

use Esendex;

/**
 * Il y a 2 comportements différents selon que le message envoyé est de type
 * Esendex\Model\DispatchMessage ou un tableau de Esendex\Model\DispatchMessage. Dans le
 * premier cas on fait appel à la classe mère qui doit être initialisée par son
 * constructeur. Mais la propriété $reference étant "private", on la surcharge dans cette
 * classe pour le cas où l'on doit traiter un tableau de messages et on surcharge la
 * méthode encode().
 *
 * @author admin
 */
class DispatchXmlParser extends Esendex\Parser\DispatchXmlParser implements
    ApiSmsInterface
{

    private $reference;

    function __construct($accountReference)
    {
        parent::__construct($accountReference);
        $this->reference = $accountReference;
    }

    /**
     * Traite un message adressé à un ou à plusieurs destinataires
     *
     * @param Esendex\Model\DispatchMessage|DispatchArrayMessage $messages
     *
     * @return string Retourne une chaîne XML basée sur un élément SimpleXML ou false si
     *         erreur
     * @throw ArgumentException
     * {@inheritdoc}
     * @see \Esendex\Parser\DispatchXmlParser::encode()
     */
    public function encode($messages)
    {
        if ($messages instanceof Esendex\Model\DispatchMessage) {
            return parent::encode($messages);
        }
        $doc = new \SimpleXMLElement(
            "<?xml version=\"1.0\" encoding=\"utf-8\"?><messages />", 0, false,
            Esendex\Model\Api::NS);
        $doc->addAttribute("xmlns", Esendex\Model\Api::NS);
        $doc->accountreference = $this->reference;
        $cmpt = 0;
        $originator = $messages->getOriginator();
        if ($originator != null) {
            if (ctype_digit($originator)) {
                if (strlen($originator) > 20)
                    throw new ArgumentException("Numeric originator must be <= 20 digits");
            } else {
                if (strlen($originator) > 11)
                    throw new ArgumentException(
                        "Alphanumeric originator must <= 11 characters");
                if (! preg_match("/^[a-zA-Z0-9\*\$\?\!\"\#\%\&_\-\,\.\s@'\+]{1,11}$/",
                    $originator))
                    throw new ArgumentException(
                        "Alphanumeric originator contains invalid character(s)");
            }
            $doc->from = $originator;
        }
        foreach ($messages as $message) {
            $cmpt ++;
            $error_msg = "Destinataire n° $cmpt :\n";
            if (strlen($message->recipient()) < 1)
                throw new ArgumentException($error_msg . "Recipient is invalid");
            if ($message->validityPeriod() > 72)
                throw new ArgumentException(
                    $error_msg . "Validity too long, must be less or equal to than 72");

            if ($message->characterSet() != null)
                $doc->characterset = $message->characterSet();

            $child = $doc->addChild("message");
            $child->to = $message->recipient();
            $child->body = $message->body();
            $child->type = $message->type();
            if ($message->validityPeriod() > 0)
                $child->validity = $message->validityPeriod();
            if ($message->language() != null)
                $child->lang = $message->language();
            if ($message->retries() != null)
                $child->retries = $message->retries();
        }

        return $doc->asXML();
    }

    /**
     * Modifie la structure du retour : un tableau de la forme ['batchid'=>string,
     * 'results'=> Esendex\Model\ResultItem[]]
     *
     * {@inheritdoc}
     * @see \Esendex\Parser\DispatchXmlParser::parse()
     */
    public function parse($xml)
    {
        $headers = simplexml_load_string($xml);
        if ($headers->getName() != "messageheaders")
            throw new XmlException("Xml is missing <messageheaders /> root element");
        $resultat = [
            'batchid' => current($headers->attributes()['batchid'])
        ];
        $results = [];
        foreach ($headers->messageheader as $header) {
            $results[] = new Esendex\Model\ResultItem($header["id"], $header["uri"]);
        }
        $resultat['results'] = $results;
        return $resultat;
    }
}