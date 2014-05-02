<?php
/**
 * Controleur du module SbmInstallation
 *
 *
 * @project sbm
 * @package module/SbmInstallation/src/SbmInstallation/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 janv. 2014
 * @version 2014-1
 */
namespace SbmInstallation\Controller;

use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use SbmInstallation\Model\Create;
use SbmInstallation\Model\Exception;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        //$result = $this->getDbDesign();
        $result = array();
        return new ViewModel(array('args' => $result));
    }
    
    public function createAction()
    {
        $create = new Create($this->getDbConfig(), $this->getDbAdapter(), $this->getDbDesign());
        $result = $create->run();
        return new ViewModel(array('args' => $result));
    }
        
    /**
     * Renvoie l'adapter donné par le ServiceManager factories
     * 
     * @return \Zend\Db\Adapter\Adapter
     */
    private function getDbAdapter()
    {
        return $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    }
    
    /**
     * Renvoie un tableau ayant pour clés
     * - 'database' : le nom de la base de données
     * - 'prefix'   : le préfixe des tables et des vues pour cette application
     * - 'definer'  : le DEFINER des vues Mysql
     * 
     * @return array
     */
    private function getDbConfig()
    {
        $config = $this->getServiceLocator()->get('config');
        return $config['db'];
    }
    
    /**
     * Renvoie un tableau décrivant la structure des tables et des vues définies dans SbmInstallation/config/db_design.
     * (voir SbmInstallation/config/db_design/README.txt)
     * 
     * @return array
     */
    private function getDbDesign()
    {
        $config = $this->getServiceLocator()->get('config');
        return $config['db_design'];
    }
}