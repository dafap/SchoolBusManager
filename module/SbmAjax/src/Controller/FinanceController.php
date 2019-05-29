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
 * @date 29 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmAjax\Controller;

use SbmBase\Model\Session;
use Zend\Json\Json;

class FinanceController extends AbstractActionController
{

    const ROUTE = 'sbmajaxfinance';

    /**
     * ajax - cocher la case paiement de scolarites à condition que le montant déjà payé
     * soit suffisant pour couvrir la somme due.
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkpaiementscolariteAction()
    {
        $millesime = Session::get('millesime');
        try {
            $eleveId = $this->params('eleveId');
            $responsableId = $this->params('responsableId');
            // die(var_dump($eleveId, $responsableId));
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setPaiement($millesime,
                $eleveId, 1);
            $resultats = $this->db_manager->get(
                \SbmCommun\Model\Db\Service\Query\Paiement\Calculs::class)->getResultats(
                $responsableId);
            $inscrits = $resultats->getAbonnements('inscrits')['montantAbonnements'];
            $sommeDue = $inscrits + $resultats->getMontantDuplicatas();
            $dejaPaye = $resultats->getPaiementsMontant();
            $preinscrits = $resultats->getAbonnements('tous')['montantAbonnements'] -
                $inscrits;
            if ($sommeDue <= $dejaPaye) {
                return $this->getResponse()->setContent(
                    Json::encode(
                        [
                            'inscrits' => $inscrits,
                            'preinscrits' => $preinscrits,
                            'success' => 1
                        ]));
            } else {
                $this->db_manager->get('Sbm\Db\Table\Scolarites')->setPaiement($millesime,
                    $eleveId, 0);
                return $this->getResponse()->setContent(
                    Json::encode(
                        [
                            'cr' => 'Impossible ! Le coût dépasserait le total encaissé',
                            'success' => 0
                        ]));
            }
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - décocher la case paiement de scolarites à condition qu'il n'y ait pas de
     * duplicatas
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckpaiementscolariteAction()
    {
        $millesime = Session::get('millesime');
        try {
            $eleveId = $this->params('eleveId');
            $responsableId = $this->params('responsableId');
            $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
            $scolarite = $tScolarites->getRecord(
                [
                    'millesime' => $millesime,
                    'eleveId' => $eleveId
                ]);
            if ($scolarite->duplicata) {
                return $this->getResponse()->setContent(
                    Json::encode(
                        [
                            'cr' => 'Impossible ! Cet élève a des duplicatas de carte de transport facturés.',
                            'success' => 0
                        ]));
            }
            $tScolarites->setPaiement($millesime, $eleveId, 0);
            $resultats = $this->db_manager->get(
                \SbmCommun\Model\Db\Service\Query\Paiement\Calculs::class)->getResultats(
                $responsableId);
            $inscrits = $resultats->getAbonnements('inscrits')['montantAbonnements'];
            $preinscrits = $resultats->getAbonnements('tous')['montantAbonnements'] -
                $inscrits;
            return $this->getResponse()->setContent(
                Json::encode(
                    [
                        'inscrits' => $inscrits,
                        'preinscrits' => $preinscrits,
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
     * ajax - cocher la case sélection dans la liste des notifications de paiement en
     * ligne
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
     * ajax - décocher la case sélection dans la liste des notifications de paiement en
     * ligne
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
        $result = $tEleves->getElevesPreinscritsWithGrille($responsableId);
        $data = [];
        foreach ($result as $row) {
            $data[] = [
                'eleveId' => $row['eleveId'],
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'grilleTarif' => $row['grilleTarif'],
                'duplicata' => $row['duplicata']
            ];
        }
        return $this->getResponse()->setContent(
            Json::encode([
                'data' => $data,
                'success' => 1
            ]));
    }

    /**
     * Cette méthode reçoit en post :
     *
     * @formatter off
     * - responsableId
     * - eleveIds : tableau encodé JSON des eleveId à prendre en compte
     * @formatter on
     * et renvoie le montant à payer en tenant compte des duplicatas
     */
    public function calculmontantAction()
    {
        $responsableId = $this->params('responsableId', - 1);
        $aEleveId = json_decode($this->params('eleveIds', '[]'));
        if ($responsableId == - 1) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'total' => 0,
                    'paye' => 0,
                    'solde' => 0,
                    'success' => 0
                ]));
        }
        $resultat = $this->db_manager->get(
            \SbmCommun\Model\Db\Service\Query\Paiement\Calculs::class)->getResultats(
            $responsableId, $aEleveId);
        return $this->getResponse()->setContent(
            Json::encode(
                [
                    'total' => $resultat->getMontantTotal('liste'),
                    'paye' => $resultat->getPaiementsMontant(),
                    'solde' => $resultat->getSolde('liste'),
                    'success' => 1
                ]));
    }
}