<?php
/**
 * Extension de la classe du SDK pour interroger sur un n° de téléphone
 *
 * @project sbm
 * @package
 * @filesource MessageHeaderService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model;

use Esendex;

class MessageHeaderService extends Esendex\MessageHeaderService
{

    private $authentication;

    private $httpClient;

    private $parser;

    /**
     * Nécessité de redéclarer les prpriétés privées dans le constructeur
     *
     * @param Esendex\Authentication\IAuthentication $authentication
     * @param Esendex\Http\IHttp $httpClient
     * @param Esendex\Parser\MessageHeaderXmlParser $parser
     */
    public function __construct(Esendex\Authentication\IAuthentication $authentication,
        Esendex\Http\IHttp $httpClient = null,
        Esendex\Parser\MessageHeaderXmlParser $parser = null)
    {
        parent::__construct($authentication, $httpClient, $parser);
        $this->authentication = $authentication;
        $this->httpClient = (isset($httpClient)) ? $httpClient : new Esendex\Http\HttpClient(
            true);
        $this->parser = (isset($parser)) ? $parser : new Esendex\Parser\MessageHeaderXmlParser();
    }

    public function query(string $telephone)
    {
        $uri = Esendex\Http\UriBuilder::serviceUri(self::SERVICE_VERSION, self::SERVICE,
            array(), $this->httpClient->isSecure());
        $uri .= "?$telephone";
        $xml = $this->httpClient->get($uri, $this->authentication);
        $headers = simplexml_load_string($xml);
        foreach ($headers as $header) {
            $reponse[] = $this->parser->parseHeader($header);
        }
        return $reponse;
    }
}