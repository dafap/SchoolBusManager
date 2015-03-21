<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmFront/Model
 * @filesource Authenticate.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 févr. 2015
 * @version 2015-1
 */
namespace SbmFront\Model;


use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Authentication\Adapter\DbTable;
use Zend\Session\Container;

class Authenticate extends Mdp implements ServiceLocatorAwareInterface
{
    const SESSION_AUTH_NAMESPACE = 'sbm_auth';
    private $sm;
    private $session;
    
    public function __construct($chars = 'abcdefghjkmnpqrstuvwxyz', $nums = '0123456789', $syms = '!@#$%^&*()-+?')
    {
        $this->session = new Container(self::SESSION_AUTH_NAMESPACE);
        parent::__construct($chars, $nums, $syms);
    }
    
    /**
     * Vérifie si l'email et le mot de passe permettent de se loger. 
     * Si oui, met les données de l'utilisateur en session et note le login dans la table.
     * Si non, renvoie faux
     * 
     * @param array $params
     * tableau dont des clés sont 'email' et 'mdp'. Tout le reste sera ignoré.
     * 
     * @throws Exception
     * @return boolean
     */
    public function authenticate($params)
    {
        if (!\is_array($params) || !isset($params['email']) || !isset($params['mdp']))
        {
            throw new Exception(__METHOD__ . 'Paramètres d\'identification incorrects. L\'email et le mot de passe sont attendus dans un tableau.');
        }
        $tableUsers = $this->getServiceLocator()->get('Sbm\Db\Table\Users');
        list($hash, $gds) = $tableUsers->getMdpGdsByEmail($params['email']);
        if (self::verify($params['mdp'], $hash, $gds)) {
            $odata = $tableUsers->getRecordByEmail($params['email']);
            $this->setAuth($odata->getArrayCopy());
            $odata->completeForLogin();
            $tableUsers->saveRecord($odata);
            return true;
        } else {
            return false;
        }
    }
    
    public function authenticateByToken($token)
    {
        if (\is_null($token)) return false;
        $tableUsers = $this->getServiceLocator()->get('Sbm\Db\Table\Users');
        try {
            $odata = $tableUsers->getRecordByToken($token);
            $odata->clearToken();
            $this->setAuth($odata->getArrayCopy());
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function hasIdentity()
    {
        return isset($this->session->auth);
    }
    
    public function getIdentity()
    {
        return $this->session->auth;
    }
    
    public function clearIdentity()
    {
        unset($this->session->auth);
    }
    
    public function getUserId()
    {
        return $this->session->auth['userId'];
    }
    
    public function refreshIdentity()
    {
        $tableUsers = $this->getServiceLocator()->get('Sbm\Db\Table\Users');
        try {
            $odata = $tableUsers->getRecord($this->getUserId());
            $this->setAuth($odata->getArrayCopy());
        } catch (Exception $e) {
            $this->clearIdentity();
        }
    }
    
    public function getCategorieId()
    {
        return $this->session->auth['categorieId'];
    }
    
    private function setAuth($data)
    {
        unset($data['mdp']);
        unset($data['token']);
        $this->session->auth = $data;
    }
    
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->sm = $serviceLocator;
    }
    
    public function getServiceLocator()
    {
        return $this->sm;
    }
}