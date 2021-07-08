<?php
/**
 * Signature d'un mail avec les entêtes DKIM
 * Se référer à la rfc4871
 *
 * @project sbm
 * @packageSbmMail/src/Model
 * @filesource Dkim.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @see Milan Divkovic (https://github.com/metalinspired/laminas-dkim)
 * @date 7 juil. 2021
 * @version 2021-2.6.3
 */
namespace SbmMail\Model;

use Zend\Mail\Message;
use Zend\Mail\Header\HeaderInterface;

class Dkim
{

    const EOL = "\r\n";

    private $private_key;

    private $params;

    /**
     *
     * @var Header\Dkim
     */
    private $dkim_header;

    /**
     *
     * @var string
     */
    private $canonized_headers;

    public function __construct(array $config)
    {
        $this->init();
        if (isset($config['params']) && is_array($config['params'])) {
            $this->setParams($config['params']);
        }
        if (isset($config['private_key']) && ! empty($config['private_key'])) {
            $this->setPrivateKey($config['private_key']);
        } else {
            $this->private_key = false;
        }
    }

    /**
     * Donne le format du tableau $params, avec les valeurs par défaut pour les clés v et
     * a
     */
    private function init()
    {
        $this->params = [
            'v' => '1',
            'a' => 'rsa-sha256',
            'd' => '',
            'h' => '',
            's' => ''
        ];
    }

    /**
     * Indique si DKIM est à appliquer
     *
     * @return boolean
     */
    public function useDkim(): bool
    {
        return ! empty($this->params['d']) && ! empty($this->params['h']) &&
            ! empty($this->params['s']) && ! empty($this->private_key);
    }

    /**
     * Ajoute l'entête DKIM dans le message si DKIM est correctement configuré et renvoie le message
     *
     * @param \Zend\Mail\Message $mail
     */
    public function sign(Message $mail): Message
    {
        if (! $this->useDkim()) {
            return $mail;
        }
        $this->canonizeBody($mail)
            ->addUnsignedDkimHeader($mail)
            ->canonizeHeaders($mail)
            ->setSignature($mail);
        return $mail;
    }

    private function canonizeBody(Message $mail)
    {
        $body = $mail->getBody();
        if ($body instanceof \Zend\Mime\Message) {
            $body = $body->generateMessage(self::EOL);
        }
        // In PCRE \R matches \n, \r and \r\n.
        $mail->setBody(trim(preg_replace('#\R#u', self::EOL, $body)) . self::EOL);
        return $this;
    }

    private function addUnsignedDkimHeader(Message $mail)
    {
        $params = [
            'v' => $this->getParam('v'),
            'a' => $this->getParam('a'),
            'bh' => $this->getBodyHash($mail),
            'c' => 'relaxed',
            'd' => $this->getParam('d'),
            'h' => $this->getParam('h'),
            's' => $this->getParam('s'),
            'b' => ''
        ];
        $string = '';
        foreach ($params as $key => $value) {
            $string .= sprintf('%s=%s; ', $key, $value);
        }
        $this->setDkimHeader(new Header\Dkim(substr(trim($string), 0, - 1)));
        $mail->getHeaders()->addHeader($this->getDkimHeader());
        return $this;
    }

    private function canonizeHeaders(Message $mail)
    {
        $dkimSignatureKey = strtolower($this->getDkimHeader()->getFieldName());
        $headersToSign = explode(':', $this->getParam('h'));
        if (! in_array($dkimSignatureKey, $headersToSign)) {
            $headersToSign[] = $dkimSignatureKey;
        }
        foreach ($headersToSign as $headerName) {
            $headerName = strtolower($headerName);
            $header = $mail->getHeaders()->get($headerName);
            if ($header instanceof HeaderInterface) {
                $this->canonized_headers .= sprintf("%s:%s\r\n", $header->getFieldName(),
                    preg_replace('#\s+#', ' ',
                        $header->getFieldValue(HeaderInterface::FORMAT_ENCODED)));
            }
        }
        return $this;
    }

    /**
     *
     * @param \Zend\Mail\Message $mail
     * @return self
     */
    private function setSignature(Message $mail): self
    {
        $signature = '';
        openssl_sign($this->canonized_headers, $signature, $this->getPrivateKey(),
            OPENSSL_ALGO_SHA256);
        $signature = trim(chunk_split(base64_encode($signature), 73, ' '));
        $headers = $mail->getHeaders();
        $headers->removeHeader($this->getDkimHeader()
            ->getFieldName());
        $headerArray = $headers->toArray(HeaderInterface::FORMAT_ENCODED);
        array_unshift($headerArray,
            new Header\Dkim(
                sprintf('%s%s;', $this->getDkimHeader()->getFieldValue(), $signature)));
        $headers->clearHeaders()->addHeaders($headerArray);
        return $this;
    }

    /**
     *
     * @return \SbmMail\Model\Header\Dkim
     */
    private function setDkimHeader(Header\Dkim $dkim_header): self
    {
        $this->dkim_header = $dkim_header;
        return $this;
    }

    /**
     * Initialise la clé privée au bon format (resource ou OpenSSLAsymmetricKey selon la
     * version de PHP) ou false si la clé privée DKIM n'est pas bien configurée.
     * La clé peut contenir ou non les -----BEGIN RSA PRIVATE KEY----- et -----END RSA
     * PRIVATE KEY-----
     * Elle peut être donnée sous une ligne où chaque tranche est séparée du reste par un
     * espace ou en multilignes.
     *
     * @param string $pk
     *            clé privée (avec ou sans BEGIN et END)
     * @return \SbmMail\Model\Dkim
     */
    public function setPrivateKey(string $pk): self
    {
        $begin_rsa = '-----BEGIN RSA PRIVATE KEY-----';
        $end_rsa = '-----END RSA PRIVATE KEY-----';
        $pk = trim(str_replace([
            $begin_rsa,
            $end_rsa
        ], '', $pk));
        $pk = implode("\r", explode(' ', $pk));
        $pk = <<<PKEY
        $begin_rsa
        $pk
        $end_rsa
        PKEY;
        $this->private_key = openssl_pkey_get_private($pk);
        return $this;
    }

    /**
     * Ne sont prises en compte que les clés présentes dans le modèle formaté dans la
     * méthode init()
     *
     * @param string $key
     * @param string $value
     * @return \SbmMail\Model\Dkim
     */
    public function setParam(string $key, string $value): self
    {
        if (empty($this->params)) {
            $this->init();
        }
        if (array_key_exists($key, $this->params)) {
            $this->params[$key] = $value;
        }
        return $this;
    }

    /**
     * Le tableau $params est un tableau associatif.
     * Seules les valeurs de type string sont prises en compte.
     *
     * @param array $params
     * @return \SbmMail\Model\Dkim
     */
    public function setParams(array $params): self
    {
        if (! empty($params)) {
            foreach ($params as $key => $value) {
                if (is_string($value)) {
                    $this->setParam($key, $value);
                }
            }
        }
        return $this;
    }

    /**
     * Get Message body (sha256) hash.
     *
     * @param \Zend\Mail\Message $message
     * @return string
     */
    private function getBodyHash(Message $message): string
    {
        return base64_encode(pack("H*", hash('sha256', $message->getBody())));
    }

    /**
     *
     * @return \SbmMail\Model\Header\Dkim
     */
    protected function getDkimHeader(): Header\Dkim
    {
        return $this->dkim_header;
    }

    /**
     *
     * @param string $key
     * @return string
     */
    protected function getParam(string $key): string
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }
        return '';
    }

    /**
     *
     * @return array
     */
    protected function getParams(): array
    {
        return $this->params;
    }

    /**
     *
     * @return boolean|resource
     */
    protected function getPrivateKey()
    {
        return $this->private_key;
    }
}