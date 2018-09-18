<?php
/**
 * Actions destinées aux réponses à des demandes ajax par les parents
 *
 * Le layout est désactivé dans ce module
 * 
 * @project sbm
 * @package SbmAjax/Controller
 * @filesource ParentController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmAjax\Controller;

use Zend\Json\Json;

class ParentController extends AbstractActionController
{

    const ROUTE = 'sbmajaxparent';

    public function getcommunesforselectAction()
    {
        $queryCommunes = $this->db_manager->get('Sbm\Db\Select\Communes');
        $communes = $queryCommunes->codePostal($this->params('codePostal'));
        $communes = array_flip($communes);
        return $this->getResponse()->setContent(
            Json::encode([
                'data' => $communes,
                'success' => 1
            ]));
    }
}