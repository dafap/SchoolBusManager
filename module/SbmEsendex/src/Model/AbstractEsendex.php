<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 *
 * @project sbm
 * @package
 * @filesource AbstractEsendex.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model;

use Esendex\Authentication\LoginAuthentication;
use Esendex\DispatchService;

class AbstractEsendex
{
    private $service;

    /**
     *
     * @param string $accountReference
     * @param string $username
     * @param string $password
     */
    public function init(string $accountReference, string $username, string $password)
    {
        $authentication = new LoginAuthentication($accountReference, $username, $password);
        $this->service = new DispatchService($authentication);
        return $this;
    }

    public function getService()
    {
        return $this->service;
    }
}