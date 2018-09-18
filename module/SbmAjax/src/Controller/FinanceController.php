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
 * @date 9 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmAjax\Controller;

use SbmBase\Model\Session;
use Zend\Db\Sql\Where;
use Zend\Json\Json;

class FinanceController extends AbstractActionController
{

    const ROUTE = 'sbmajaxfinance';

    /**
     * ajax - cocher la case paiement de scolarites
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkpaiementscolariteAction()
    {
        $millesime = Session::get('millesime');
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setPaiement($millesime,
                $eleveId, 1);
            return $this->getResponse()->setContent(Json::encode([
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

    /**
     * ajax - décocher la case paiement de scolarites
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckpaiementscolariteAction()
    {
        $millesime = Session::get('millesime');
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setPaiement($millesime,
                $eleveId, 0);
            return $this->getResponse()->setContent(Json::encode([
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

    /**
     * ajax - cocher la case sélection des organismes
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkselectionorganismeAction()
    {
        try {
            $organismeId = $this->params('organismeId');
            $this->db_manager->get('Sbm\Db\Table\Organismes')->setSelection($organismeId,
                1);
            return $this->getResponse()->setContent(Json::encode([
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

    /**
     * ajax - décocher la case sélection des organismes
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckselectionorganismeAction()
    {
        try {
            $organismeId = $this->params('organismeId');
            $this->db_manager->get('Sbm\Db\Table\Organismes')->setSelection($organismeId,
                0);
            return $this->getResponse()->setContent(Json::encode([
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

    /**
     * ajax - cocher la case sélection des paiements
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkselectionpaiementAction()
    {
        try {
            $paiementId = $this->params('paiementId');
            $this->db_manager->get('Sbm\Db\Table\Paiements')->setSelection($paiementId, 1);
            return $this->getResponse()->setContent(Json::encode([
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

    /**
     * ajax - décocher la case sélection des paiements
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckselectionpaiementAction()
    {
        try {
            $paiementId = $this->params('paiementId');
            $this->db_manager->get('Sbm\Db\Table\Paiements')->setSelection($paiementId, 0);
            return $this->getResponse()->setContent(Json::encode([
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

    /**
     * ajax - cocher la case sélection des tarifs
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkselectiontarifAction()
    {
        try {
            $tarifId = $this->params('tarifId');
            $this->db_manager->get('Sbm\Db\Table\Tarifs')->setSelection($tarifId, 1);
            return $this->getResponse()->setContent(Json::encode([
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

    /**
     * ajax - décocher la case sélection des tarifs
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckselectiontarifAction()
    {
        try {
            $tarifId = $this->params('tarifId');
            $this->db_manager->get('Sbm\Db\Table\Tarifs')->setSelection($tarifId, 0);
            return $this->getResponse()->setContent(Json::encode([
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

    /**
     * ajax - cocher la case sélection dans la liste des notifications de paiement en ligne
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkselectionnotificationAction()
    {
        try {
            $id = $this->params('id');
            $this->db_manager->get('SbmPaiement\Plugin\Table')->setSelection($id, 1);
            return $this->getResponse()->setContent(Json::encode([
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

    /**
     * ajax - décocher la case sélection dans la liste des notifications de paiement en ligne
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckselectionnotificationAction()
    {
        try {
            $id = $this->params('id');
            $this->db_manager->get('SbmPaiement\Plugin\Table')->setSelection($id, 0);
            return $this->getResponse()->setContent(Json::encode([
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

    /**
     * ajax - liste des enfants preinscrits d'un responsable
     *
     * @param int $responsableId
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function listepreinscritsAction()
    {
        $responsableId = $this->params('responsableId', - 1);
        $tEleves = $this->db_manager->get('Sbm\Db\Query\ElevesScolarites');
        $result = $tEleves->getElevesPreinscritsWithMontant($responsableId);
        $data = [];
        foreach ($result as $row) {
            $data[] = [
                'eleveId' => $row['eleveId'],
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'montant' => $row['montant'],
                'nomTarif' => $row['nomTarif']
            ];
        }
        $nbDuplicatas = $tEleves->getNbDuplicatas($responsableId);
        if ($nbDuplicatas) {
            $montantUnitaire = $this->db_manager->get('Sbm\Db\Table\Tarifs')->getMontant(
                'duplicata');
            $montantDuplicatas = $nbDuplicatas * $montantUnitaire;
            // duplicatas déjà encaissés
            $where = new Where();
            $millesime = Session::get('millesime');
            $as = sprintf('%d-%d', $millesime, $millesime + 1);
            $where->equalTo('anneeScolaire', $as)->equalTo('responsableId', $responsableId);
            $totalEncaisse = $this->db_manager->get('Sbm\Db\Table\Paiements')->total(
                $where);
            $totalInscriptions = $tEleves->getMontantElevesInscrits($responsableId);
            $duplicatasPayes = $totalEncaisse - $totalInscriptions;
            // reste à payer
            $montantDuplicatas -= $duplicatasPayes;
        } else {
            $montantDuplicatas = 0.00;
        }
        return $this->getResponse()->setContent(
            Json::encode(
                [
                    'duplicatas' => $montantDuplicatas,
                    'data' => $data,
                    'success' => 1
                ]));
    }
}