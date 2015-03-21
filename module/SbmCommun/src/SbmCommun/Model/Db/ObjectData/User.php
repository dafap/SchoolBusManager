<?php
/**
 * Objet contenant les données à manipuler pour la table Users
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource User.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 févr. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\ObjectData;

use Zend\Math\Rand;
use SbmCommun\Model\DateLib;
use SbmFront\Model\Mdp;

class User extends AbstractObjectData
{
    public function __construct()
    {
        $this->setObjName(__CLASS__);
        $this->setIdFieldName('userId');
    }
    
    public function completeToCreate()
    {
        $this->setToken();
        $this->dateCreation = DateLib::nowToMysql();
        $this->gds = Rand::getString(8);
        $this->categorieId = 1;
    }
    
    public function completeToModif()
    {
        $this->dateModification = DateLib::nowToMysql();
    }
    
    public function setToken()
    {
        $this->token = md5(uniqid(mt_rand(1000, 9999), true));
        $this->tokenalive = true;
    }
    
    public function clearToken()
    {
        $this->token = null;
        $this->tokenalive = false;       
    }
    
    public function confirme()
    {
        $this->clearToken();
        $this->confirme = true;
        $this->active = true;
    }
    
    public function completeForLogin()
    {
        $this->datePreviousLogin = $this->dateLastLogin;
        $this->previousIp = $this->adresseIp;
        $this->dateLastLogin = DateLib::nowToMysql();
        $this->adresseIp = $_SERVER['REMOTE_ADDR'];
    }
    
    public function setMdp($userId, $mdp, $gds)
    {
        $this->userId = $userId;
        $this->mdp = Mdp::crypteMdp($mdp, $gds);
    }
}