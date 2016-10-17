<?php
/**
 * Opérations sur le responsable correspondant à l'utilisateur autentifié.
 *
 * La correspondance se fait par l'email.
 * Compatible ZF3
 * 
 * @project sbm
 * @package SbmParent/Model
 * @filesource Responsable.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 oct. 2016
 * @version 2016-2.2.1
 */
namespace SbmFront\Model\Responsable;

use SbmBase\Model\Session;

class Responsable
{

    const SESSION_RESPONSABLE_NAMESPACE = 'sbmparent_responsable';

    /**
     *
     * @var \Zend\Authentication\AuthenticationService
     */
    private $authenticate_by;

    /**
     *
     * @var \SbmCommun\Model\Db\Service\Table\Vue\Responsables
     */
    private $vue_responsable;

    /**
     * 
     * @var array
     */
    private $responsable;

    /**
     * Le constructeur s'assure qu'un responsable est en session et qu'il correspond bien
     * à l'utilisateur authentifié (vérif par l'email).
     *
     * Si ce n'est pas le cas, le responsable correspondant à l'utilisateur authentifié est en session.
     *
     * S'il n'existe pas de responsable correspondant à cet utilisateur, une exception est
     * lancée. Il faudra la traiter en demandant la création du responsable.
     *
     * @param \Zend\Authentication\AuthenticationService $authenticate_by            
     * @param \SbmCommun\Model\Db\Service\Table\Vue $vue_responsable            
     *
     * @throws Exception
     */
    public function __construct($authenticate_by, $vue_responsable)
    {
        $this->authenticate_by = $authenticate_by;
        $this->vue_responsable = $vue_responsable;
        $this->responsable = Session::get('responsable', [], self::SESSION_RESPONSABLE_NAMESPACE);
        $this->init();
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->responsable)) {
            return $this->responsable[$name];
        }
        
        $trace = debug_backtrace();
        trigger_error('Propriété non-définie via __get() : ' . $name . ' dans ' . $trace[0]['file'] . ' à la ligne ' . $trace[0]['line'], E_USER_NOTICE);
        return null;
    }

    public function __set($name, $value)
    {
        $this->responsable['name'] = $value;
        Session::set('responsable', $this->responsable, self::SESSION_RESPONSABLE_NAMESPACE);
    }

    public function __unset($name)
    {
        unset($this->responsable['name']);
        Session::set('responsable', $this->responsable, self::SESSION_RESPONSABLE_NAMESPACE);
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->responsable);
    }

    /**
     * Renvoie le tableau des données du responsable
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->responsable;
    }

    public function refresh()
    {
        $this->responsable = false;
        $this->init();
    }

    /**
     * Si invalide, va chercher le responsable correspondant à l'utilisateur autentifié dans la table des responsables
     *
     * @throws Exception S'il n'y a pas de responsable correspondant à l'utilisateur autentifié
     */
    private function init()
    {
        $email = $this->authenticate_by->getIdentity()['email'];
        if ($this->invalid($email)) {
            $r = $this->vue_responsable->getRecordByEmail($email);
            if (empty($r)) {
                $this->responsable = [];
                throw new Exception('Responsable à créer');
            } else {
                $this->responsable = $r->getArrayCopy();
                Session::set('responsable', $this->responsable, self::SESSION_RESPONSABLE_NAMESPACE);
            }
        }
    }

    /**
     * Si invalide, supprime 'responsable' de la session et renvoie vrai
     *
     * @param string $email            
     * @return boolean
     */
    private function invalid($email)
    {
        if (! is_array($this->responsable) || ! array_key_exists('email', $this->responsable) || $this->responsable['email'] != $email) {
            Session::remove('responsable', self::SESSION_RESPONSABLE_NAMESPACE);
            return true;
        }
        return false;
    }
}
 