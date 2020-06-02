<?php
/**
 * Cette classe surcharge la classe du Sdk Esendex pour traiter l'envoi d'un groupe de messages.
 *
 * @project sbm
 * @package SbmEsendex/src/Model
 * @filesource DispatchService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model;

use Esendex;

/**
 * Le but est de modifier le type du paramètre de la méthode send() qui pourra être un
 * tableau et l'objet DispatchXmlParser qui traitera un tableau de DispatchMessage. La
 * surcharge n'est utile que pour conserver le type initial et les constantes. Les
 * propriétés de la classe mère étant "private" on est obligé de surcharger toutes les
 * propriétés et toutes les méthodes.
 *
 * @author admin
 */
class DispatchService extends Esendex\DispatchService implements ApiSmsInterface
{

    private $authentication;

    private $httpClient;

    private $parser;

    /**
     *
     * @param Esendex\Authentication\IAuthentication $authentication
     * @param Esendex\Http\IHttp $httpClient
     * @param Esendex\Parser\DispatchXmlParser $parser
     */
    public function __construct(Esendex\Authentication\IAuthentication $authentication,
        Esendex\Http\IHttp $httpClient = null,
        Esendex\Parser\DispatchXmlParser $parser = null)
    {
        $this->authentication = $authentication;
        $this->httpClient = (isset($httpClient)) ? $httpClient : new Esendex\Http\HttpClient(
            true);
        $this->parser = (isset($parser)) ? $parser : new DispatchXmlParser(
            $authentication->accountReference());
    }

    /**
     * Modifie la structure du retour : un tableau de la forme ['batchid'=>string,
     * 'results'=> Esendex\Model\ResultItem[]]
     *
     *
     * @param Esendex\Model\DispatchMessage|DispatchArrayMessage $message
     * @return array
     * @throw ArgumentException
     * @throw EsendexException
     * {@inheritdoc}
     * @see \Esendex\DispatchService::send()
     */
    public function send($message)
    {
        if (! $message instanceof DispatchArrayMessage &&
            ! $message instanceof Esendex\Model\DispatchMessage) {
            throw new ArgumentException(
                'Erreur. Le paramètre passé n\'est pas du type attendu.');
        }
        $xml = $this->parser->encode($message);
        $uri = Esendex\Http\UriBuilder::serviceUri(self::DISPATCH_SERVICE_VERSION,
            self::DISPATCH_SERVICE, null, $this->httpClient->isSecure());

        $result = $this->httpClient->post($uri, $this->authentication, $xml);

        $array = $this->parser->parse($result);

        if (count($array) >= 1) {
            return $array;
        } else {
            throw new EsendexException("Error parsing the dispatch result", null,
                array(
                    'data_returned' => $result
                ));
        }
    }

    /**
     * Get the number of remaining credits for your account
     *
     * @return int
     */
    public function getCredits()
    {
        try {
            $uri = Esendex\Http\UriBuilder::serviceUri(self::ACCOUNTS_SERVICE_VERSION,
                self::ACCOUNTS_SERVICE, null, $this->httpClient->isSecure());

            $xml = $this->httpClient->get($uri, $this->authentication);
            $accounts = new \SimpleXMLElement($xml);
            foreach ($accounts->account as $account) {
                if (strcasecmp($account->reference,
                    $this->authentication->accountReference()) == 0) {
                    return intval($account->messagesremaining, 10);
                }
            }
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}