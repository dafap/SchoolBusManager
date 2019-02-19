<?php
/**
 * Gestion des sessions
 *
 * Ecriture et lecture dans une session pour un namespace donné.
 * Par défaut, le namespace SBM_DG_SESSION est utilisé.
 * 
 * @project sbm
 * @package SbmBase\Model
 * @filesource Session.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmBase\Model;

use Zend\Session\Container;

abstract class Session
{

    const SBM_DG_SESSION = 'sbm_dg_session';

    /**
     * Renvoie le paramètre en session ou la valeur par défaut s'il n'est pas défini
     *
     * @param string $param
     *            Nom du paramètre demandé
     * @param mixed $default
     *            Valeur à renvoyer si le paramètre n'est pas défini
     * @param string|null $sessionNamespace
     *            namespace de la session (par défaut valeur fixée par le constante de cette classe
     *            SBM_DG_SESSION)
     *            
     * @return mixed
     */
    public static function get($param, $default = null,
        $sessionNamespace = self::SBM_DG_SESSION)
    {
        $sessionNamespace = preg_replace('/[^a-z0-9_\\\\]/i', '', $sessionNamespace);
        $session = new Container($sessionNamespace);
        return isset($session->{$param}) ? $session->{$param} : $default;
    }

    /**
     * Place la valeur en session dans le paramètre indiqué
     *
     * @param string $param
     *            nom du paramètre
     * @param mixed $value
     *            valeur à mettre en session
     * @param string|null $sessionNamespace
     *            namespace de la session (par défaut valeur fixée par le constante de cette classe
     *            SBM_DG_SESSION)
     */
    public static function set($param, $value, $sessionNamespace = self::SBM_DG_SESSION)
    {
        $sessionNamespace = preg_replace('/[^a-z0-9_\\\\]/i', '', $sessionNamespace);
        $session = new Container($sessionNamespace);
        $session->{$param} = $value;
    }

    /**
     * Supprime le paramètre indiqué de la session
     *
     * @param string $param
     * @param string $sessionNamespace
     */
    public static function remove($param, $sessionNamespace = self::SBM_DG_SESSION)
    {
        $sessionNamespace = preg_replace('/[^a-z0-9_\\\\]/i', '', $sessionNamespace);
        $session = new Container($sessionNamespace);
        unset($session->{$param});
    }
}