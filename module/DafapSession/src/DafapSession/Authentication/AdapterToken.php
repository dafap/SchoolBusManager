<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource AdapterToken.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 mai 2015
 * @version 2015-1
 */
namespace DafapSession\Authentication;

use Zend\Authentication\Adapter\ValidatableAdapterInterface;
use Zend\Authentication\Result;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AdapterToken implements ValidatableAdapterInterface, ServiceLocatorAwareInterface
{

    /**
     * C'est le token
     *
     * @var string
     */
    protected $identity;

    /**
     * Inutile, gardé uniquement par compatibilité
     *
     * @var unknown
     */
    protected $credential;

    /**
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $sm;

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
        if (empty($this->identity)) {
            throw new Exception(__METHOD__ . 'Paramètre d\'entrée incorrect. Le token n\'a pas été donné.');
        }
        $tUsers = $this->getServiceLocator()->get('Sbm\Db\Table\Users');
        try {
            $odata = $tUsers->getRecordByToken($this->identity);
            $identity = $odata->clearToken()->getArrayCopy();
            unset($identity->token);
            return new Result(Result::SUCCESS, $identity, array('Identification confirmée.'));
        } catch (\Exception $e) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, '', array('Impossible de se connecter.'));
        }
    }

    /**
     * Le token
     * 
     * (non-PHPdoc)
     * @see \Zend\Authentication\Adapter\ValidatableAdapterInterface::getIdentity()
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * Ici, $identity est le token (de type string)
     * (non-PHPdoc)
     * 
     * @see \Zend\Authentication\Adapter\ValidatableAdapterInterface::setIdentity()
     */
    public function setIdentity($identity)
    {
        if (! is_string($identity)) {
            throw new Exception('Token : une chaine de caractère est attendue.');
        }
        $this->identity = $identity;
        return $this;
    }

    /**
     * Gardé par compatibilité mais non utilisé !
     *
     * (non-PHPdoc)
     * 
     * @see \Zend\Authentication\Adapter\ValidatableAdapterInterface::getCredential()
     */
    public function getCredential()
    {
        return $this->credential;
    }

    /**
     * Inutile car non utilisé.
     *
     * (non-PHPdoc)
     * 
     * @see \Zend\Authentication\Adapter\ValidatableAdapterInterface::setCredential()
     */
    public function setCredential($credential)
    {
        $this->credential = $credential;
    }
}