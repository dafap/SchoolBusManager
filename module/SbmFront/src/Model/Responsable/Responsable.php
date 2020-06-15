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
 * @date 15 juin 2020
 * @version 2020-2.5.7
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
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     *
     * @var array
     */
    private $responsable;

    /**
     * Le constructeur s'assure qu'un responsable est en session et qu'il correspond bien
     * à l'utilisateur authentifié (vérif par l'email). Si ce n'est pas le cas, le
     * responsable correspondant à l'utilisateur authentifié est en session. S'il n'existe
     * pas de responsable correspondant à cet utilisateur, une exception est lancée. Il
     * faudra la traiter en demandant la création du responsable.
     *
     * @param \Zend\Authentication\AuthenticationService $authenticate_by
     * @param \SbmCommun\Model\Db\Service\DbManager $db_manager
     *
     * @throws \SbmFront\Model\Responsable\Exception (par la method init())
     */
    public function __construct($authenticate_by, $db_manager)
    {
        $this->authenticate_by = $authenticate_by;

        $this->db_manager = $db_manager;
        $this->responsable = Session::get('responsable', [],
            self::SESSION_RESPONSABLE_NAMESPACE);
        $this->init();
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->responsable)) {
            return $this->responsable[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Propriété non-définie via __get() : ' . $name . ' dans ' . $trace[0]['file'] .
            ' à la ligne ' . $trace[0]['line'], E_USER_NOTICE);
        return null;
    }

    public function __set($name, $value)
    {
        $this->responsable[$name] = $value;
        Session::set('responsable', $this->responsable,
            self::SESSION_RESPONSABLE_NAMESPACE);
    }

    public function __unset($name)
    {
        unset($this->responsable[$name]);
        Session::set('responsable', $this->responsable,
            self::SESSION_RESPONSABLE_NAMESPACE);
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
     * Si invalide, va chercher le responsable correspondant à l'utilisateur autentifié
     * dans la table des responsables
     *
     * @throws \SbmFront\Model\Responsable\Exception S'il n'y a pas de responsable
     *         correspondant à l'utilisateur autentifié
     */
    private function init()
    {
        $email = $this->authenticate_by->getIdentity()['email'];
        if ($this->invalid($email)) {
            $vue_responsable = $this->db_manager->get('Sbm\Db\Vue\Responsables');
            $r = $vue_responsable->getRecordByEmail($email);
            if (empty($r)) {
                $this->responsable = [];
                throw new Exception('Responsable à créer');
            } else {
                $this->responsable = $r->getArrayCopy();
                // application du zonage
                $this->appliqueZonage();
                // mise en session
                Session::set('responsable', $this->responsable,
                    self::SESSION_RESPONSABLE_NAMESPACE);
            }
        }
    }

    private function appliqueZonage()
    {
        $tzonage = $this->db_manager->get('Sbm\Db\Table\Zonage');
        $liste_communes_zonees = $tzonage->getCommunesZonees();
        if (! in_array($this->responsable['communeId'], $liste_communes_zonees)) {
            $this->responsable['zonage'] = false;
            return; // commune non zonée
        }
        $this->responsable['zonage'] = true;
        $za = new \SbmCommun\Filter\ZoneAdresse();
        $adresseL1 = $za->filter($this->responsable['adresseL1']);
        $this->responsable['inscriptionenligne'] = $tzonage->isInscriptionEnLigne(
            $this->responsable['communeId'], $adresseL1);
        $this->responsable['paiementenligne'] = $tzonage->isPaiementEnLigne(
            $this->responsable['communeId'], $adresseL1);
        if ($this->responsable['adresseL2']) {
            // pour la ligne 2
            $adresseL2 = $za->filter($this->responsable['adresseL2']);
            $this->responsable['inscriptionenligne'] |= $tzonage->isInscriptionEnLigne(
                $this->responsable['communeId'], $adresseL2);
            $this->responsable['paiementenligne'] |= $tzonage->isPaiementEnLigne(
                $this->responsable['communeId'], $adresseL2);
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
        if (! is_array($this->responsable) ||
            ! array_key_exists('email', $this->responsable) ||
            $this->responsable['email'] != $email) {
            Session::remove('responsable', self::SESSION_RESPONSABLE_NAMESPACE);
            return true;
        }
        return false;
    }

    /**
     * Supprime le responsable en session
     */
    public function clear()
    {
        Session::remove('responsable', self::SESSION_RESPONSABLE_NAMESPACE);
    }
}
