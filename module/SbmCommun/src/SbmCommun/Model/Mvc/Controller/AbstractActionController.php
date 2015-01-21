<?php
/**
 * Description
 *
 *
 * @project sbm
 * @package 
 * @filesource AbstractActionController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Mvc\Controller;

use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;
use Zend\Session\Container as SessionContainer;

/**
 * Quelques méthodes utiles
 *
 * @author admin
 *        
 */
abstract class AbstractActionController extends ZendAbstractActionController
{
    const SBM_DG_SESSION = 'sbm_dg_session';
    
    /**
     * Renvoie une chaine de la forme 'module_controller_action_item'
     *
     * @param string $item            
     * @return string
     */
    protected function getSessionNamespace($item = null)
    {
        $args = array(
            $this->getModuleControllerName(),
            $this->getCurrentActionFromRoute()
        );
        if (! is_null($item)) {
            $args[] = $item;
        }
        return str_replace('-', '_', implode('_', $args));
    }

    /**
     * Renvoie une chaine de la forme 'module_controller'
     * exemple : sbmfront_index
     *
     * @return string
     */
    protected function getModuleControllerName()
    {
        $parts = explode('\\', strtolower(get_class($this))); // de la forme {'sbmfront', 'controller', 'indexcontroller'}
        unset($parts[1]); // supprime 'controller'
        return substr_replace(implode('_', $parts), '', - 10); // supprime 'controller' à la fin
    }

    /**
     * Renvoie le nom de l'action ou index par défaut
     *
     * @return string
     */
    protected function getCurrentActionFromRoute()
    {
        return $this->params('action', 'index');
    }

    /**
     * Renvoie le paramètre en session ou la valeur par défaut s'il n'est pas défini
     *
     * @param $param 
     *      Nom du paramètre demandé
     * @param $default
     *      Valeur à renvoyer si le paramètre n'est pas défini
     * 
     * @return int|boolean
     */
    protected function getSbmSessionGenerale($param, $default = null)
    {
        $session = new SessionContainer(self::SBM_DG_SESSION);
        return isset($session->{$param}) ? $session->{$param} : $default;
    }
    
    /**
     * Place la valeur en session générale dans le paramètre indiqué
     * 
     * @param string $param
     *      nom du paramètre
     * @param mixed $value
     *      valeur à mettre en session
     */
    protected function setSbmSessionGenerale($param, $value)
    {
        $session = new SessionContainer(self::SBM_DG_SESSION);
        $session->{$param} = $value;
    }
}