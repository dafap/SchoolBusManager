<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 *
 * @project project_name
 * @package package_name
 * @filesource Mailman.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 juin 2019
 * @version 2019-1
 */
namespace SbmMail\Model;

use Zend\Http\Client;

class Mailman
{

    private $client;

    /**
     * modèle de construction de l'url par sprintf
     *
     * @var string
     */
    private $uri;

    private $adminpw;

    /**
     *
     * @param string $uri
     *            adresse de l'api comprenant le protocole et le domaine conduisant à
     *            mailman. Exemple : https://dafap.fr/mailman
     * @param string $nom_liste
     *            nom de la liste. Exemple : liste_dafap.fr. On le trouvera dans
     *            l'interface d'administration de mailman, archives de la liste, version
     *            téléchargeable (URL)
     * @param string $adminpw
     *            mot de passe administrateur de la liste. On le définit dans l'interface
     *            d'administration de la liste, mots de passe
     */
    function __construct($uri, $nom_liste, $adminpw)
    {
        $options = [
            'adapter' => new Client\Adapter\Curl(),
            'curloptions' => [
                CURLOPT_FOLLOWLOCATION => true
            ]
        ];
        $this->client = new Client(null, $options);
        $this->uri = "$uri/%s/$nom_liste/";
        $this->adminpw = $adminpw;
    }

    public function subscribe($who, $welcome = true, $notify = true)
    {
        $who = (array) $who;

        $data = [
            'csrf_token' => $this->getCSRF(
                $this->host . '/admin/' . $this->group . '/members/add'),
            'subscribees' => implode('\n', $who),
            'subscribe_or_invite' => 0,
            'send_welcome_msg_to_this_batch' => 0,
            'send_notifications_to_list_owner' => 0,
            'setmemberopts_btn' => 'Inscrire ces adresses'
        ];
        $this->client->send();
    }

    public function unsubscribe($who, $bye = true, $notify = true)
    {
        if (! is_array($who))
            $who = array(
                $who
            );

        $data = array(
            'csrf_token' => $this->getCSRF(
                $this->host . '/admin/' . $this->group . '/members/remove'),
            'unsubscribees' => join('\n', $who),
            'send_unsub_ack_to_this_batch' => (int) $bye,
            'send_unsub_notifications_to_list_owner' => (int) $notify,
            'setmemberopts_btn' => 'Submit your changes'
        );
        $this->curl->post($this->host . '/admin/' . $this->group . '/members/remove',
            $data);

        // Handle 401, 403, 500
        if ($this->curl->error) {
            throw new Exception($this->curl->httpErrorMessage, $this->curl->httpError);
        }
        return true;
    }

    public function updateName($email, $username)
    {
        $this->curl->post($this->host . '/options/' . $this->group . '/' . $email,
            array(
                'fullname' => $username,
                'change-of-address' => 'Change My Address and Name'
            ));
        // Handle 401, 403, 500
        if ($this->curl->error) {
            throw new Exception($this->curl->httpErrorMessage, $this->curl->httpError);
        }
        return true;
    }

    public function listSubscribers()
    {
        $this->curl->get($this->host . '/roster/' . $this->group);
        // Handle 401, 403, 500
        if ($this->curl->error) {
            throw new Exception($this->curl->httpErrorMessage, $this->curl->httpError);
        }
        if (preg_match_all(
            '%<a.*?href=(?:"|\').*?/options/' . $this->group .
            '/.*?(?:--at--|@).*?(?:"|\')-*?>(.*?(?:(?: |)*at(?: |)*|@).*?)</a>%',
            $this->curl->response, $matches)) {
            foreach ($matches[1] as &$value) {
                $value = str_replace(' at ', '@', $value);
                $value = str_replace(' ', '', $value);
            }
            return $matches[1];
        }
        return array();
    }

    private function getCSRF($where)
    {
        $this->curl->get($where);
        if ($this->curl->error) {
            throw new Exception($this->curl->httpErrorMessage, $this->curl->httpError);
        }
        if (preg_match('/<input.*?name=(\'|")csrf_token("|\'").*?>/',
            $this->curl->response, $match)) {
            if (preg_match('/value=(?:\'|")(.*?)(?:\'|")/', $match[0], $match)) {
                return $match[1];
            }
        }
        return false;
    }
}