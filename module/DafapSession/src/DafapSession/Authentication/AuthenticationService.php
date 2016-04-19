<?php
/**
 * Adaptation d'un Zend\Authentication\AuthenticationService pour ce projet
 * 
 * @project sbm
 * @package DafapSession/Authentication
 * @filesource AuthenticationService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 avr. 2016
 * @version 2016-2
 */
namespace DafapSession\Authentication;

use Zend\Authentication\AuthenticationService as ZendAuthenticationService;
use Zend\Authentication\Storage\StorageInterface;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthenticationService extends ZendAuthenticationService
{
    /**
     * C'est un db manager mais on n'utilise que sa méthode get()
     * 
     * @var ServiceLocatorInterface
     */
    private $db_manager;
    
    
    public function __construct(ServiceLocatorInterface $db_manager,StorageInterface $storage = null,AdapterInterface $adapter = null)
    {
        $this->db_manager = $db_manager;
        parent::__construct($storage, $adapter);
    }
    
    public function getUserId()
    {
        $storage = $this->getStorage();
        
        if ($storage->isEmpty()) {
            return false;
        }
        return $storage->read()['userId'];
    }

    public function getCategorieId()
    {
        $storage = $this->getStorage();
        
        if ($storage->isEmpty()) {
            return false;
        }
        return $storage->read()['categorieId'];
    }

    public function refreshIdentity()
    {
        $tUsers = $this->db_manager->get('Sbm\Db\Table\Users');
        try {
            $odata = $tUsers->getRecord($this->getUserId());
            $data = $odata->getArrayCopy();
            unset($data['mdp']);
            unset($data['token']);
            if ($this->hasIdentity()) {
                $this->clearIdentity();
            }
            $this->getStorage()->write($data);
        } catch (\Exception $e) {
            $this->clearIdentity();
        }
    }
}
