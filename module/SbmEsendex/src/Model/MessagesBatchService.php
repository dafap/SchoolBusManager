<?php
/**
 * Interrogation de l'API de Esendex pour obtenir des informations sur les batches
 *
 * Non fourni par le SDK
 *
 * @project sbm
 * @package SbmEsendex/src/Model
 * @filesource MessagesBatchService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model;

use Esendex;

class MessagesBatchService
{

    const MESSAGES_BATCH_SERVICE = 'messagebatches';

    const MESSAGES_BATCH_SERVICE_VERSION = 'v1.1';

    private $authentication;

    private $httpClient;

    private $parser;

    public function __construct(Esendex\Authentication\IAuthentication $authentication,
        Esendex\Http\IHttp $httpClient = null, MessagesBatchXmlParser $parser = null)
    {
        $this->authentication = $authentication;
        $this->httpClient = (isset($httpClient)) ? $httpClient : new Esendex\Http\HttpClient(
            true);
        $this->parser = (isset($parser)) ? $parser : new MessagesBatchXmlParser();
    }

    public function getMessagesBatch(string $batchid)
    {
        $uri = Esendex\Http\UriBuilder::serviceUri(self::MESSAGES_BATCH_SERVICE_VERSION,
            self::MESSAGES_BATCH_SERVICE, [
                $batchid
            ], $this->httpClient->isSecure());
        $data = $this->httpClient->get($uri, $this->authentication);
        return $this->parser->parse($data);
    }
}