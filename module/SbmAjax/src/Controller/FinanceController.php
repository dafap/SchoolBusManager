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
 * @date 11 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmAjax\Controller;

use SbmBase\Model\Session;
use Zend\Json\Json;

class FinanceController extends AbstractActionController
{

    const ROUTE = 'sbmajaxfinance';

    public function checkselectionplateformeAction()
    {
        try {
            $table = $this->db_manager->get('SbmPaiement\Plugin\Table');
            $tableId = $this->params($table->getIdName());
            $table->setSelection($tableId, 1);
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

    public function uncheckselectionplateformeAction()
    {
        try {
            $table = $this->db_manager->get('SbmPaiement\Plugin\Table');
            $tableId = $this->params($table->getIdName());
            $table->setSelection($tableId, 0);
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
     * ajax - cocher la case paiement de scolarites à condition que le montant déjà payé
     * soit suffisant pour couvrir la somme due.
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkpaiementscolariteAction()
    {
        try {
            $eleveId = $this->params('eleveId');
            $responsableId = $this->params('responsableId');
            $this->db_manager->get('Sbm\Paiement\MarqueEleves')->setPaiement($eleveId,
                $responsableId, true);
            $resultats = $this->db_manager->get('Sbm\Facture\Calculs')->getResultats(
                $responsableId);
            $inscrits = $resultats->getAbonnementsMontant(0, 'inscrits');
            $sommeDue = $inscrits + $resultats->getMontantDuplicatas();
            $dejaPaye = $resultats->getPaiementsMontant();
            $preinscrits = $resultats->getAbonnementsMontant(0, 'tous') - $inscrits;
            if ($sommeDue <= $dejaPaye) {
                return $this->getResponse()->setContent(
                    Json::encode(
                        [
                            'inscrits' => $inscrits,
                            'preinscrits' => $preinscrits,
                            'success' => 1
                        ]));
            } else {
                $this->db_manager->get('Sbm\Paiement\MarqueEleves')->setPaiement($eleveId,
                    $responsableId, false);
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
            $r = $this->db_manager->get('Sbm\Db\Table\Eleves')->estResponsable(
                $responsableId, $eleveId);
            if ($this->db_manager->get('Sbm\Db\Table\Scolarites')->getRecord(
                [
                    'millesime' => $millesime,
                    'eleveId' => $eleveId
                ])->{"duplicataR$r"}) {
                return $this->getResponse()->setContent(
                    Json::encode(
                        [
                            'cr' => 'Impossible ! Cet élève a des duplicatas de carte de transport facturés.',
                            'success' => 0
                        ]));
            }
            $this->db_manager->get('Sbm\Paiement\MarqueEleves')->setPaiement($eleveId,
                $responsableId, false);
            $resultats = $this->db_manager->get('Sbm\Facture\Calculs')->getResultats(
                $responsableId);
            $inscrits = $resultats->getAbonnementsMontant(0, 'inscrits');
            $preinscrits = $resultats->getAbonnementsMontant(0, 'tous') - $inscrits;
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
                'reduction' => $row['reduction'] ? 'Réduit' : 'Normal',
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
        $resultat = $this->db_manager->get('Sbm\Facture\Calculs')->getResultats(
            $responsableId, $aEleveId);
        return $this->getResponse()->setContent(
            Json::encode(
                [
                    'total' => $resultat->getMontantTotal(0, 'liste'),
                    'paye' => $resultat->getPaiementsMontant(),
                    'solde' => $resultat->getSolde(0, 'liste'),
                    'success' => 1
                ]));
    }
}