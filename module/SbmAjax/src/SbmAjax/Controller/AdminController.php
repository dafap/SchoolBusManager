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
 * @date 3 avr. 2018
 * @version 2018-2.4.0
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
            $this->db_manager->get('Sbm\Db\Table\Users')->setSelection($userId, 1);
            return $this->getResponse()->setContent(
                Json::encode([
                    'success' => 1
                ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode(
                    [
                        'cr' => $e->getMessage(),
                        'success' => 0
                    ]));
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
            $this->db_manager->get('Sbm\Db\Table\Users')->setSelection($userId, 0);
            return $this->getResponse()->setContent(
                Json::encode([
                    'success' => 1
                ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode(
                    [
                        'cr' => $e->getMessage(),
                        'success' => 0
                    ]));
        }
    }
}