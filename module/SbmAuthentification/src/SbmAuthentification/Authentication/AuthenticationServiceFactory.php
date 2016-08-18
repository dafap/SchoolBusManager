<?php
/**
 * Service fournissant un AuthenticationService
 *
 * (version adaptée pour ZF3)
 * 
 * @project sbm
 * @package SbmAuthentification/Authentication
 * @filesource AuthenticationServiceFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 août 2016
 * @version 2016-2.2.0
 */
namespace SbmAuthentification\Authentication;

use Zend\ServiceManager\FactoryInterface;
use Zend\Authentication\Storage\Session;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthenticationServiceFactory implements FactoryInterface
{

    const SESSION_AUTH_NAMESPACE = 'sbm_auth';

    /**
     * 
     * @var Zend\ServiceManager\ServiceLocatorInterface
     */
    private $db_manager;
    
    /**
     *
     * @var \Zend\Authentication\AuthenticationService
     */
    private $authService;

    /**
     *
     * @var \Zend\Authentication\Adapter\ValidatableAdapterInterface
     */
    private $adapterEmail;
    
    /**
     * 
     * @var \Zend\Authentication\Adapter\ValidatableAdapterInterface
     */
    private $adapterToken;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->adapterEmail = null;
        $this->adapterToken = null;
        $this->db_manager = $serviceLocator->get('Sbm\DbManager');
        $this->authService = new AuthenticationService($this->db_manager, new Session(self::SESSION_AUTH_NAMESPACE));
        return $this;
    }

    /**
     * Choisit l'adapter à utiliser : 'email' ou 'token'
     *
     * @param string $adapter
     *            enum 'email' | 'token'
     * @return \Zend\Authentication\AuthenticationService
     */
    public function by($adapter = 'email')
    {
        if ($adapter == 'token') {
            if (empty($this->adapterToken)) {
                $this->adapterToken = new AdapterToken($this->db_manager);
            }
            $this->authService->setAdapter($this->adapterToken);
        } else {
            if (empty($this->adapterEmail)) {
                $this->adapterEmail = new AdapterEmail($this->db_manager);
            }
            $this->authService->setAdapter($this->adapterEmail);
        }
        return $this->authService;
    }
}