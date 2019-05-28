<?php
/**
 * Adapter pour une autentification par email
 *
 * (version adaptée pour ZF3)
 *
 * @project sbm
 * @package SbmAuthentification/Authentication
 * @filesource AdapterEmail.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmAuthentification\Authentication;

use SbmAuthentification\Model\Mdp;
use Zend\Authentication\Result;
use Zend\Authentication\Adapter\ValidatableAdapterInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AdapterEmail implements ValidatableAdapterInterface
{

    /**
     * Correspond à l'email
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
     * C'est un db manager mais on n'utilise que la méthode get()
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $db_manager;

    /**
     *
     * @var \SbmAuthentification\Model\Mdp
     */
    protected $mdp;

    /**
     * Passe éventuellement un tableau de lettres (chars), chiffres (nums) et signes (syms)
     * autorisés pour la constitution du mot de passe.
     * Pour chaque catégorie, les valeurs autorisées sont regroupées dans une chaine de caractères.
     * Dans les valeurs par défaut, par de o, pas de i, pas de l. Les ensembles autorisées de
     * lettres minuscules et majuscules sont les mêmes.
     *
     * @param
     *            Zend\ServiceManager\ServiceLocatorInterface
     * @param array $args
     */
    public function __construct(ServiceLocatorInterface $db_manager, array $args = [])
    {
        $this->db_manager = $db_manager;

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
            throw new Exception\RuntimeException('Le mail n\'a pas été donné.');
        } elseif (empty($this->credential)) {
            throw new Exception\RuntimeException('Le mot de passe n\'a pas été donné.');
        }
        $tUsers = $this->db_manager->get('Sbm\Db\Table\Users');
        $result = $tUsers->getMdpGdsByEmail($this->identity);
        if ($result) {
            list ($hash, $gds) = $result;
            if ($this->mdp->verify($this->credential, $hash, $gds)) {
                $odata = $tUsers->getRecordByEmail($this->identity);
                $identity = $odata->getArrayCopy();
                unset($identity['mdp']);
                unset($identity['token']);
                $odata->clearToken()->completeForLogin();
                $tUsers->saveRecord($odata);
                return new Result(Result::SUCCESS, $identity, [
                    'Identification réussie.'
                ]);
            } else {
                return new Result(Result::FAILURE_CREDENTIAL_INVALID, '',
                    [
                        'Mot de passe incorrect ou compte bloqué.'
                    ]);
            }
        } else {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, '',
                [
                    'Email inconnu ou compte inactif.'
                ]);
        }
    }

    /**
     * L'email
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
     * On donne un tableau contenant l'email (clé 'email') ou une chaine représentant l'email
     *
     * (non-PHPdoc)
     *
     * @see \Zend\Authentication\Adapter\ValidatableAdapterInterface::setIdentity()
     *
     * @throws \SbmAuthentification\Authentication\Exception\InvalidArgumentException
     */
    public function setIdentity($identity)
    {
        if (is_string($identity)) {
            $this->identity = $identity;
        } elseif (is_array($identity) && array_key_exists('email', $identity)) {
            $this->identity = $identity['email'];
        } else {
            throw new Exception\InvalidArgumentException(
                __METHOD__ .
                'Paramètres d\'identification incorrects. L\'email n\'a pas été donné.');
        }
        return $this;
    }

    /**
     * Le mot de passe en clair
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
     * On donne un tableau contenant le mot de passe (clé 'mdp') ou le mot de passe en clair
     *
     * (non-PHPdoc)
     *
     * @see \Zend\Authentication\Adapter\ValidatableAdapterInterface::setCredential()
     *
     * @throws \SbmAuthentification\Authentication\Exception\InvalidArgumentException
     */
    public function setCredential($credential)
    {
        if (is_string($credential)) {
            $this->credential = $credential;
        } elseif (is_array($credential) && array_key_exists('mdp', $credential)) {
            $this->credential = $credential['mdp'];
        } else {
            throw new Exception\InvalidArgumentException(
                __METHOD__ .
                'Paramètres d\'identification incorrects. Le mot de passe n\'a pas été donné.');
        }
        return $this;
    }

    /**
     * Renvoie un objet Mdp
     *
     * @return \SbmAuthentification\Model\Mdp
     */
    public function getMdp()
    {
        return $this->mdp;
    }
}