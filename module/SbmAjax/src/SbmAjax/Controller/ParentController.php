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
 * @date 15 mai 2015
 * @version 2015-1
 */
namespace SbmAjax\Controller;

use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class ParentController extends AbstractActionController
{

    const ROUTE = 'sbmajaxparent';

    public function getcommunesforselectAction()
    {
        $queryCommunes = $this->getServiceLocator()->get('Sbm\Db\Select\Communes');
        $communes = $queryCommunes->codePostal($this->params('codePostal'));
        $communes = array_flip($communes);
        return $this->getResponse()->setContent(Json::encode(array(
            'data' => $communes,
            'success' => 1
        )));
    }
}