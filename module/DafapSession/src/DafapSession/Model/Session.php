<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource Session.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 févr. 2015
 * @version 2015-1
 */
namespace DafapSession\Model;

use Zend\Session\Container;

abstract class Session {
    const SBM_DG_SESSION = 'sbm_dg_session';
    
    /**
     * Renvoie le paramètre en session ou la valeur par défaut s'il n'est pas défini
     *
     * @param $param Nom
     *            du paramètre demandé
     * @param $default Valeur
     *            à renvoyer si le paramètre n'est pas défini
     * @param string|null $sessionNamespace
     *            namespace de la session (par défaut valeur fixée par le constante de cette classe SBM_DG_SESSION)
     *
     * @return int|boolean
     */
    public static function get($param, $default = null, $sessionNamespace = self::SBM_DG_SESSION)
    {
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
     *            namespace de la session (par défaut valeur fixée par le constante de cette classe SBM_DG_SESSION)
     */
    public static function set($param, $value, $sessionNamespace = self::SBM_DG_SESSION)
    {
        $session = new Container($sessionNamespace);
        $session->{$param} = $value;
    }
}