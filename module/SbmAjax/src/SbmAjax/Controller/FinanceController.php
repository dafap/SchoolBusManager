<?php
/**
 * Actions destinées aux réponses à des demandes ajax pour les paiements et les tarifs
 *
 * Le layout est désactivé dans ce module
 * 
 * @project sbm
 * @package SbmAjax/Controller
 * @filesource FinanceController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 mai 2015
 * @version 2015-1
 */
namespace SbmAjax\Controller;

use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class FinanceController extends AbstractActionController
{

    const ROUTE = 'sbmajaxfinance';

    /**
     * ajax - cocher la case sélection des paiements
     *
     * @method GET
     * @return dataType json
     */
    public function checkselectionpaiementAction()
    {
        try {
            $paiementId = $this->params('paiementId');
            $this->getServiceLocator()
                ->get('Sbm\Db\Table\Paiements')
                ->setSelection($paiementId, 1);
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
     * ajax - décocher la case sélection des paiements
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckselectionpaiementAction()
    {
        try {
            $paiementId = $this->params('paiementId');
            $this->getServiceLocator()
                ->get('Sbm\Db\Table\Paiements')
                ->setSelection($paiementId, 0);
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
     * ajax - cocher la case sélection des tarifs
     *
     * @method GET
     * @return dataType json
     */
    public function checkselectiontarifAction()
    {
        try {
            $tarifId = $this->params('tarifId');
            $this->getServiceLocator()
                ->get('Sbm\Db\Table\Tarifs')
                ->setSelection($tarifId, 1);
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
     * ajax - décocher la case sélection des tarifs
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckselectiontarifAction()
    {
        try {
            $tarifId = $this->params('tarifId');
            $this->getServiceLocator()
                ->get('Sbm\Db\Table\Tarifs')
                ->setSelection($tarifId, 0);
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
     * ajax - cocher la case sélection dans la liste des notifications de paiement en ligne
     *
     * @method GET
     * @return dataType json
     */
    public function checkselectionnotificationAction()
    {
        try {
            $id = $this->params('id');
            $this->getServiceLocator()
                ->get('SbmPaiement\Plugin\Table')
                ->setSelection($id, 1);
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
     * ajax - décocher la case sélection dans la liste des notifications de paiement en ligne
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckselectionnotificationAction()
    {
        try {
            $id = $this->params('id');
            $this->getServiceLocator()
                ->get('SbmPaiement\Plugin\Table')
                ->setSelection($id, 0);
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
     * ajax - liste des enfants preinscrits d'un responsable
     *
     * @param int $responsableId            
     * @return dataType json
     */
    public function listepreinscritsAction()
    {
        $responsableId = $this->params('responsableId', - 1);
        $tEleves = $this->getServiceLocator()->get('Sbm\Db\Query\ElevesScolarites');
        $result = $tEleves->getElevesPreinscritsWithMontant($responsableId);
        $data = array();
        foreach ($result as $row) {
            $data[] = array(
                'eleveId' => $row['eleveId'],
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'montant' => $row['montant'],
                'nomTarif' => $row['nomTarif']
            );
        }
        return $this->getResponse()->setContent(Json::encode(array(
            'data' => $data,
            'success' => 1
        )));
    }
}