<?php
/**
 * Adapter pour une autentification par token
 *
 * (version adptée pour ZF3)
 * 
 * @project sbm
 * @package SbmAuthentification/Authentication
 * @filesource AdapterToken.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmAuthentification\Authentication;

use Zend\Authentication\Result;
use Zend\Authentication\Adapter\ValidatableAdapterInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AdapterToken implements ValidatableAdapterInterface
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
     * @var string
     */
    protected $credential;

    /**
     * C'est un db manager mais on n'utilise que la méthode get()
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $db_manager;

    /**
     * Constructeur passant le serviceManager pour pouvoir retrouver la table users
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $db_manager
     */
    public function __construct(ServiceLocatorInterface $db_manager)
    {
        $this->db_manager = $db_manager;
    }

    /**
     * Renvoie un Result contenant le code, le tableau de données à stocker et le message.
     * S'il y a une erreur, le tableau de données à stocker est remplacé par une chaine vide.
     *
     * (non-PHPdoc)
     *
     * @see \Zend\Authentication\Adapter\AdapterInterface::authenticate()
     * 
     * @throws \SbmAuthentification\Authentication\Exception\RuntimeException
     */
    public function authenticate()
    {
        if (empty($this->identity)) {
            throw new Exception\RuntimeException(
                __METHOD__ . 'Paramètre d\'entrée incorrect : pas de token.');
        }
        $tUsers = $this->db_manager->get('Sbm\Db\Table\Users');
        try {
            $odata = $tUsers->getRecordByToken($this->identity);
            $identity = $odata->clearToken()->getArrayCopy();
            unset($identity->token);
            return new Result(Result::SUCCESS, $identity, [
                'Identification confirmée.'
            ]);
        } catch (\Exception $e) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, '',
                [
                    'Impossible de se connecter.'
                ]);
        }
    }

    /**
     * Le token
     *
     * (non-PHPdoc)
     *
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
     * 
     * @throws \SbmAuthentification\Authentication\Exception\InvalidArgumentException
     */
    public function setIdentity($identity)
    {
        if (! is_string($identity)) {
            throw new Exception\InvalidArgumentException('Token : une chaine de caractère est attendue.');
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