<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource AdapterEmail.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 mai 2015
 * @version 2015-1
 */
namespace DafapSession\Model\Authentication;

use Zend\Authentication\Adapter\ValidatableAdapterInterface;
use Zend\Authentication\Result;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapSession\Model\Mdp;
use DafapSession\Model\Exception;

class AdapterEmail implements ValidatableAdapterInterface, ServiceLocatorAwareInterface
{

    /**
     *
     * @var string
     */
    protected $identity;

    /**
     *
     * @var string
     */
    protected $credential;

    /**
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $sm;

    /**
     *
     * @var \DafapSession\Model\Mdp
     */
    protected $mdp;

    /**
     * Passe éventuellement un tableau de lettres (chars), chiffres (nums) et signes (syms) autorisés pour la constitution du mot de passe.
     * Pour chaque catégorie, les valeurs autorisées sont regroupées dans une chaine de caractères.
     * Dans les valeurs par défaut, par de o, pas de i, pas de l. Les ensembles autorisées de lettres minuscules et majuscules sont les mêmes.
     *
     * @param array $args            
     */
    public function __construct(array $args = array())
    {
        $chars = 'abcdefghjkmnpqrstuvwxyz';
        if (array_key_exists('chars', $args))
            $chars = $args['chars'];
        $nums = '0123456789';
        if (array_key_exists('nums', $args))
            $chars = $args['nums'];
        $syms = '!@#$%^&*()-+?';
        if (array_key_exists('syms', $args))
            $chars = $args['syms'];
        
        $this->mdp = new Mdp($chars, $nums, $syms);
    }

    public function getServiceLocator()
    {
        return $this->sm;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->sm = $serviceLocator;
    }

    /**
     * Renvoie un Result contenant le code, le tableau de données à stocker et le message.
     * S'il y a une erreur, le tableau de données à stocker est remplacé par une chaine vide.
     * 
     * (non-PHPdoc)
     * @see \Zend\Authentication\Adapter\AdapterInterface::authenticate()
     */
    public function authenticate()
    {
        if (empty($this->identity) || empty($this->mdp)) {
            throw new Exception(__METHOD__ . 'Paramètres d\'identification incorrects. L\'email ou le mot de passe n\'ont pas été donnés.');
        }
        $tUsers = $this->getServiceLocator()->get('Sbm\Db\Table\Users');
        $result = $tUsers->getMdpGdsByEmail($this->identity);
        if ($result) {
            list ($hash, $gds) = $result;
            if ($this->mdp->verify($this->credential, $hash, $gds)) {
                $odata = $tUsers->getRecordByEmail($this->identity);
                $identity =$odata->getArrayCopy();
                unset($identity['mdp']);
                unset($identity['token']);
                $odata->completeForLogin();
                $tUsers->saveRecord($odata);
                return new Result(Result::SUCCESS, $identity, array('Identification réussie.'));
            } else {
                return new Result(Result::FAILURE_CREDENTIAL_INVALID, '', array('Mot de passe incorrect ou compte bloqué.'));
            }
        } else {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, '', array('Email inconnu.'));
        }
    }

    /**
     * L'email
     * 
     * (non-PHPdoc)
     * @see \Zend\Authentication\Adapter\ValidatableAdapterInterface::getIdentity()
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * On donne un tableau contenant l'email (clé 'email') ou une chaine représentant l'email
     * 
     * (non-PHPdoc)
     * @see \Zend\Authentication\Adapter\ValidatableAdapterInterface::setIdentity()
     */
    public function setIdentity($identity)
    {
        if (is_string($identity)) {
            $this->identity = $identity;
        } elseif (is_array($identity) && array_key_exists('email', $identity)) {
            $this->identity = $identity['email'];
        } else {
            throw new Exception(__METHOD__ . 'Paramètres d\'identification incorrects. L\'email n\'a pas été donné.');
        }
        return $this;
    }

    /**
     * Le mot de passe en clair
     * 
     * (non-PHPdoc)
     * @see \Zend\Authentication\Adapter\ValidatableAdapterInterface::getCredential()
     */
    public function getCredential()
    {
        return $this->credential;
    }

    /**
     * On donne un tableau contenant le mot de passe (clé 'mdp') ou le mot de passe en clair
     * 
     * (non-PHPdoc)
     * @see \Zend\Authentication\Adapter\ValidatableAdapterInterface::setCredential()
     */
    public function setCredential($credential)
    {
        if (is_string($credential)) {
            $this->credential = $credential;
        } elseif (is_array($credential) && array_key_exists('mdp', $credential)) {
            $this->credential = $credential['mdp'];
        } else {
            throw new Exception(__METHOD__ . 'Paramètres d\'identification incorrects. Le mot de passe n\'a pas été donné.');
        }
        return $this;
    }
    
    /**
     * Renvoie un objet Mdp
     * 
     * @return \DafapSession\Model\Mdp
     */
    public function getMdp()
    {
        return $this->mdp;
    }
}