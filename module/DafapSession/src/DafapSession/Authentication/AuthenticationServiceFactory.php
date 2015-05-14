<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource AuthenticationServiceFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 mai 2015
 * @version 2015-1
 */
namespace DafapSession\Model\Authentication;

use Zend\ServiceManager\FactoryInterface;
use Zend\Authentication\Storage\Session;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthenticationServiceFactory implements FactoryInterface
{

    const SESSION_AUTH_NAMESPACE = 'sbm_auth';

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
        $this->adapterEmail = $serviceLocator->get('Dafap\AdapterByEmail');
        $this->adapterToken = $serviceLocator->get('Dafap\AdapterByToken');
        $this->authService = new AuthenticationService(new Session(self::SESSION_AUTH_NAMESPACE));
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
            $this->authService->setAdapter($this->adapterToken);
        } else {
            $this->authService->setAdapter($this->adapterEmail);
        }
        return $this->authService;
    }
}