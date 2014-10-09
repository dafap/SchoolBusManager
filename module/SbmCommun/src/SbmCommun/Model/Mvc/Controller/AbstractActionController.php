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

/**
 * Quelques méthodes utiles
 * @author admin
 *
 */
abstract class AbstractActionController extends ZendAbstractActionController
{
    /**
     * Renvoie une chaine de la forme 'module_controller_action_item'
     * 
     * @param string $item
     * @return string
     */
    protected function getSessionNamespace($item = null)
    {
        $args = array($this->getModuleControllerName(), $this->getCurrentActionFromRoute());
        if (!is_null($item)) {
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
        return substr_replace(implode('_', $parts), '', -10); // supprime 'controller' à la fin
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
}