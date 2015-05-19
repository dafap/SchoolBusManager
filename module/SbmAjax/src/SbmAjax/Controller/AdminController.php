<?php
/**
 * Actions destinées aux réponses à des demandes ajax pour les administrateurs
 *
 * Le layout est désactivé dans ce module
 * 
 * @project sbm
 * @package SbmAjax/Controller
 * @filesource AdminController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 mai 2015
 * @version 2015-1
 */
namespace SbmAjax\Controller;

use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class AdminController extends AbstractActionController
{
    const ROUTE = 'sbmajaxadmin';
    
    /**
     * ajax - cocher la case sélection des users
     *
     * @method GET
     * @return dataType json
     */
    public function checkselectionuserAction()
    {
        try {
            $userId = $this->params('userId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Users')
            ->setSelection($userId, 1);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
    /**
     * ajax - décocher la case sélection des users
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckselectionuserAction()
    {
        try {
            $userId = $this->params('userId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Users')
            ->setSelection($userId, 0);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
}