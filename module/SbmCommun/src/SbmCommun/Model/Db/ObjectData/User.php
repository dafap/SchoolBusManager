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
 * @date 17 août 2016
 * @version 2016-2.2.0
 */
namespace SbmCommun\Model\Db\ObjectData;

use Zend\Math\Rand;
use SbmBase\Model\DateLib;
use SbmBase\Model\Mdp;

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
        if (! isset($this->mdp)) {
            // mot de passe obligatoire pour MySql à partir de la version 5.7
            $mdp = new Mdp();
            $this->mdp = Mdp::crypteMdp($mdp->genereMdp(18, 3, 4, 3), $this->gds);
        }
        return $this;
    }

    public function completeToModif()
    {
        $this->dateModification = DateLib::nowToMysql();
        return $this;
    }

    public function setToken()
    {
        $this->token = md5(uniqid(mt_rand(1000, 9999), true));
        $this->tokenalive = true;
        return $this;
    }

    public function clearToken()
    {
        $this->token = null;
        $this->tokenalive = false;
        return $this;
    }

    public function confirme()
    {
        $this->clearToken();
        $this->confirme = true;
        $this->active = true;
        return $this;
    }

    public function completeForLogin()
    {
        $this->datePreviousLogin = $this->dateLastLogin;
        $this->previousIp = $this->adresseIp;
        $this->dateLastLogin = DateLib::nowToMysql();
        $this->adresseIp = $_SERVER['REMOTE_ADDR'];
        return $this;
    }

    public function setMdp($userId, $mdp, $gds)
    {
        $this->userId = $userId;
        $this->mdp = Mdp::crypteMdp($mdp, $gds);
        return $this;
    }

    public function setNote($msg)
    {
        $this->note = $msg;
        return $this;
    }

    public function addNote($msg)
    {
        if (is_null($this->note)) {
            $this->setNote($msg);
        } else {
            $this->note .= "\n$msg";
        }
        return $this;
    }
}