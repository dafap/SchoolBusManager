<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource AuthenticationService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 mai 2015
 * @version 2015-1
 */
namespace DafapSession\Model\Authentication;

use Zend\Authentication\AuthenticationService as ZendAuthenticationService;
use Zend\Authentication\Storage\StorageInterface;
use Zend\Authentication\Adapter\AdapterInterface;

class AuthenticationService extends ZendAuthenticationService
{
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
        $tUsers = $this->getAdapter()
            ->getServiceLocator()
            ->get('Sbm\Db\Table\Users');
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
