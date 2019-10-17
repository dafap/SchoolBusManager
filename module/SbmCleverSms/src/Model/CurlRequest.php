<?php
/**
 * Classe des méthodes de préparation des requêtes curl
 *
 * @project sbm
 * @package SbmCleverSms/src/Model
 * @filesource CurlRequest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 oct. 2019
 * @version 2019-2.5.2
 */
namespace SbmCleverSms\Model;

class CurlRequest
{
    use \SbmCommun\Model\Log\LoggerTrait;

    /**
     * Début de l'URL de la requête
     *
     * @var string
     */
    private $api_url;

    private $username;

    private $password;

    private $ch;

    public function __construct($config)
    {
        $this->setFileLog($config['path_filelog'], 'cleversms_error.log');
        $this->api_url = $config['api_url'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->ch = null;
    }

    /**
     * Méthode d'initialisation d'une session de curl qui renvoie une resource CURL
     *
     * @param string $url
     *            Complément de l'URL de la requête
     * @param string $method
     *            GET, POST, DELETE
     * @param string $data
     *
     * @return resource
     */
    public function curlInitialize(string $url, string $method, string $data = '')
    {
        $this->ch = curl_init(rtrim($this->api_url, '/') . "/$url");
        if ($this->ch === false) {
            throw new Exception\OutOfBoundsException("Impossible d'initialiser une session cURL.");
        }
        //curl_reset($this->ch);
        if (strcasecmp($method, 'post') == 0) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->buildHeaderRequest());
        return $this;
    }

    /**
     * Méthode exécutant la requête CURL et renvoyant le résultat
     *
     * @param resource $ch
     *
     * @return Response
     */
    public function curlExec(): Response
    {
        $response = curl_exec($this->ch);
        $status = curl_getinfo($this->ch);
        curl_close($this->ch);
        return new Response($status, $response);
    }

    /**
     * Méthode qui construit le header de la requête
     *
     * @return string[]
     */
    private function buildHeaderRequest()
    {
        $authorization = base64_encode($this->username . ':' . $this->password);
        return [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . $authorization
        ];
    }
}