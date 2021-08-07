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
 * @date 7 août 2021
 * @version 2021-2.6.3
 */
namespace SbmAjax\Controller;

use Zend\Json\Json;

/**
 *
 * Attention ! Cette classe dérive d'une classe AbstractActionController spéciale pour ce
 * module
 *
 * @property \SbmCommun\Model\Db\Service\DbManager $db_manager
 *
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 *
 */
class ParentController extends AbstractActionController
{

    const ROUTE = 'sbmajaxparent';

    public function getclassesforselectAction()
    {
        try {
            $tEtablissements = $this->db_manager->get('Sbm\Db\Table\Etablissements');
            $etablissement = $tEtablissements->getRecord($this->params('etablissementId'));
            $queryClasses = $this->db_manager->get('Sbm\Db\Select\Classes');
            $classes = $queryClasses->niveau($etablissement->niveau, 'in');
            return $this->getResponse()->setContent(
                Json::encode([
                    'data' => $classes,
                    'success' => 1
                ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

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