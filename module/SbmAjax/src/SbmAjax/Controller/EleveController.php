<?php
/**
 * Actions destinées aux réponses à des demandes ajax pour les élèves et les responsables
 *
 * Le layout est désactivé dans ce module
 * 
 * @project sbm
 * @package SbmAjax/Controller
 * @filesource EleveController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 sept. 2016
 * @version 2016-2.2.0
 */
namespace SbmAjax\Controller;

use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use SbmBase\Model\Session;
use SbmCartographie\Model\Point;
use SbmCartographie\Model\SbmCartographie\Model;

class EleveController extends AbstractActionController
{

    const ROUTE = 'sbmajaxeleve';

    public function indexAction()
    {
        return array(
            'args' => $this->params('args', null)
        );
    }

    /**
     * ajax - cocher la case sélection des responsables
     *
     * @method GET
     * @return dataType json
     */
    public function checkselectionresponsableAction()
    {
        try {
            $responsableId = $this->params('responsableId');
            $this->db_manager->get('Sbm\Db\Table\Responsables')->setSelection($responsableId, 1);
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
     * ajax - décocher la case sélection des responsables
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckselectionresponsableAction()
    {
        try {
            $responsableId = $this->params('responsableId');
            $this->db_manager->get('Sbm\Db\Table\Responsables')->setSelection($responsableId, 0);
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
     * ajax - cocher la case sélection des élèves
     *
     * @method GET
     * @return dataType json
     */
    public function checkselectioneleveAction()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Eleves')->setSelection($eleveId, 1);
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
     * ajax - décocher la case sélection des élèves
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckselectioneleveAction()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Eleves')->setSelection($eleveId, 0);
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
     * ajax - change responsable
     * Renvoie les données d'un responsable dont la référence est passée par GET
     *
     * @method GET
     * @return dataType json
     */
    public function getresponsableAction()
    {
        try {
            $responsableId = $this->params('responsableId');
            $responsable = $this->db_manager->get('Sbm\Db\Vue\Responsables')->getRecord($responsableId);
            
            return $this->getResponse()->setContent(Json::encode(array_merge($responsable->getArrayCopy(), array(
                'success' => 1
            ))));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }

    /**
     * Initialise et renvoie le formulaire d'affectation
     * (méthode utilisée dans les méthodes publiques formaffectationAction() et formaffectationvalidateAction()
     *
     * @return \SbmGestion\Form\AffectationDecision
     */
    private function getFormAffectationDecision($trajet)
    {
        $values_options1 = $this->db_manager->get('Sbm\Db\Select\Stations')->ouvertes();
        $values_options2 = $this->db_manager->get('Sbm\Db\Select\Services');
        $form = new \SbmGestion\Form\AffectationDecision($trajet, 2);
        $form->remove('back');
        $form->setAttribute('action', $this->url()
            ->fromRoute(self::ROUTE, array(
            'action' => 'formaffectationvalidate'
        )));
        $form->setValueOptions('station1Id', $values_options1)
            ->setValueOptions('station2Id', $values_options1)
            ->setValueOptions('service1Id', $values_options2)
            ->setValueOptions('service2Id', $values_options2);
        return $form;
    }

    /**
     * Renvoie le code html d'un formulaire
     * $trajet est le numéro du responsable (1 ou 2)
     *
     * @method GET
     * @return dataType html
     */
    public function formaffectationAction()
    {
        $trajet = $this->params('trajet', 1);
        $station1Id = $this->params('station1Id', null);
        $station2Id = $this->params('station2Id', null);
        $aData = array(
            'millesime' => Session::get('millesime'),
            'eleveId' => $this->params('eleveId', 0),
            'trajet' => $trajet,
            'jours' => '31', // Lu Ma Me Je Ve
            'sens' => '3', // aller-retour
            'correspondance' => $this->params('correspondance', 1),
            'responsableId' => $this->params('responsableId', 0),
            'station1Id' => $station1Id,
            'station2Id' => $station2Id,
            'service1Id' => $this->params('service1Id', null),
            'service2Id' => $this->params('service2Id', null),
            'op' => $this->params('op', null)
        );
        
        return new ViewModel(array(
            'trajet' => $trajet,
            'station1Id' => $station1Id,
            'station2Id' => $station2Id,
            'form' => $this->getFormAffectationDecision($trajet)->setData($aData),
            'is_xmlhttprequest' => 1
        ));
    }

    /**
     * Traite le post du formulaire formaffectation
     *
     * @method POST
     * @return dataType json
     */
    public function formaffectationvalidateAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();
        
        if ($request->isPost()) {
            if ($request->getPost('cancel') || $request->getPost('submit') == 'cancel') {
                $messages = 'Opération abandonnée.';
                $this->flashMessenger()->addInfoMessage($messages);
                $response->setContent(Json::encode(array(
                    'cr' => $messages,
                    'success' => 1
                )));
            } else {
                $form = $this->getFormAffectationDecision($request->getPost('trajet'));
                $form->setData($request->getPost());
                if (! $form->isValid()) {
                    $errors = $form->getMessages();
                    $messages = '';
                    foreach ($errors as $key => $row) {
                        if (! empty($row) && $key != 'submit') {
                            foreach ($row as $keyer => $rower) {
                                // save error(s) per-element that needed by Javascript
                                $messages .= $key . ' : ' . _($rower) . "\n";
                            }
                        }
                    }
                    $response->setContent(Json::encode(array(
                        'cr' => $messages,
                        'success' => 0
                    )));
                } else {
                    $tAffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
                    $oData = $tAffectations->getObjData();
                    $oData->exchangeArray($form->getData());
                    try {
                        switch ($form->getData()['op']) {
                            case 'add':
                                // ré-écrire saveRecord pour calculer le bon correspondance
                                $tAffectations->insertRecord($oData);
                                $messages = 'Nouvelle affectation enregistrée.';
                                $this->flashMessenger()->addSuccessMessage('Nouvelle affectation enregistrée.');
                                break;
                            case 'edit':
                                $tAffectations->updateRecord($oData);
                                $messages = 'Affectation modifiée.';
                                $this->flashMessenger()->addSuccessMessage('Affectation modifiée.');
                                break;
                            case 'delete':
                                // ré-écrire deleteRecord pour mettre à jour les correspondances qui restent
                                $tAffectations->deleteRecord($oData);
                                $messages = 'Affectation supprimée.';
                                $this->flashMessenger()->addSuccessMessage('Affectation supprimée.');
                                break;
                            default:
                                $messages = 'Demande incorrecte.';
                                $this->flashMessenger()->addWarningMessage('Demande incorrecte.');
                                break;
                        }
                        $response->setContent(Json::encode(array(
                            'cr' => "$messages",
                            'success' => 1
                        )));
                    } catch (\Exception $e) {
                        $this->flashMessenger()->addErrorMessage('Une erreur s\'est produite pendant le traitement de la demande.');
                        $response->setContent(Json::encode(array(
                            'cr' => $e->getMessage(),
                            'success' => 0
                        )));
                    }
                }
            }
        } else {
            $response->setContent(Json::encode(array(
                'cr' => 'Pas de post !',
                'success' => 0
            )));
        }
        
        return $response;
    }

    private function getFormPriseEnChargePaiement()
    {
        $form = new \SbmGestion\Form\Eleve\PriseEnChargePaiement();
        
        $form->setAttribute('action', $this->url()
            ->fromRoute(self::ROUTE, array(
            'action' => 'formpaiementvalidate'
        )));
        $form->setValueOptions('organismeId', $this->db_manager->get('Sbm\Db\Select\Organismes'));
        return $form;
    }

    public function formpaiementAction()
    {
        $eleveId = $this->params('eleveId', 0);
        if ($eleveId) {
            $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
            $oData = $tScolarites->getRecord(array(
                'millesime' => Session::get('millesime'),
                'eleveId' => $eleveId
            ));
            $aData = array(
                'eleveId' => $eleveId,
                'gratuit' => $oData->gratuit,
                'organismeId' => $oData->organismeId
            );
            return new ViewModel(array(
                'form' => $this->getFormPriseEnChargePaiement()->setData($aData),
                'is_xmlhttprequest' => 1
            ));
        } else {
            $response->setContent(Json::encode(array(
                'cr' => 'Pas de référence élève !',
                'success' => 0
            )));
            return $response;
        }
    }

    public function formpaiementvalidateAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();
        
        if ($request->isPost()) {
            if ($request->getPost('cancel') || $request->getPost('submit') == 'cancel') {
                $messages = 'Opération abandonnée.';
                $this->flashMessenger()->addInfoMessage($messages);
                $response->setContent(Json::encode(array(
                    'cr' => $messages,
                    'success' => 1
                )));
            } else {
                $form = $this->getFormPriseEnChargePaiement()->setData($request->getPost());
                if (! $form->isValid()) {
                    $errors = $form->getMessages();
                    $messages = '';
                    foreach ($errors as $key => $row) {
                        if (! empty($row) && $key != 'submit') {
                            foreach ($row as $keyer => $rower) {
                                // save error(s) per-element that needed by Javascript
                                $messages .= $key . ' : ' . _($rower) . "\n";
                            }
                        }
                    }
                    $response->setContent(Json::encode(array(
                        'cr' => $messages,
                        'success' => 0
                    )));
                } else {
                    $data = $form->getData();
                    $eleveId = $data['eleveId'];
                    $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
                    try {
                        $oData = $tScolarites->getRecord(array(
                            'millesime' => Session::get('millesime'),
                            'eleveId' => $eleveId
                        ));
                        $oData->gratuit = $data['gratuit'];
                        if ($data['gratuit'] == 2) {
                            $oData->organismeId = $data['organismeId'];
                        } else {
                            $oData->organismeId = 0;
                        }
                        $tScolarites->updateRecord($oData);
                        $messages = 'La prise en charge du paiement a été modifiée.';
                        $this->flashMessenger()->addSuccessMessage($messages);
                        $response->setContent(Json::encode(array(
                            'cr' => "$messages",
                            'success' => 1
                        )));
                    } catch (\Exception $e) {
                        $this->flashMessenger()->addErrorMessage('Une erreur s\'est produite pendant le traitement de la demande.');
                        $response->setContent(Json::encode(array(
                            'cr' => $e->getMessage(),
                            'success' => 0
                        )));
                    }
                }
            }
        } else {
            $response->setContent(Json::encode(array(
                'cr' => 'Pas de post !',
                'success' => 0
            )));
        }
        
        return $response;
    }

    /**
     * Renvoie la distance d'un domicile à un établissement
     * L'argument passé en GET dans args contient une chaine de la forme responsableId:valeur/etablissementId:valeur
     *
     * @method GET
     * @return dataType json
     */
    public function donnedistanceAction()
    {
        $response = $this->getResponse();
        try {
            $etablissementId = $this->params('etablissementId', false);
            $responsableId = $this->params('responsableId', false);
            $distance = 0;
            if ($etablissementId && $responsableId) {
                $responsable = $this->db_manager->get('Sbm\Db\Table\Responsables')->getRecord($responsableId);
                $origine = new Point($responsable->x, $responsable->y);
                $etablissement = $this->db_manager->get('Sbm\Db\Table\Etablissements')->getRecord($etablissementId);
                $destination = new Point($etablissement->x, $etablissement->y);
                $oOrigineDestination = $this->cartographie_manager->get('SbmCarto\DistanceEtablissements');
                $distance = $oOrigineDestination->calculDistance($origine, $destination);
                if ($distance) {
                    $distance = round($distance / 1000, 1);
                }
            }
            $response->setContent(Json::encode(array(
                'distance' => $distance,
                'success' => 1
            )));
        } catch (\Exception $e) {
            $response->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
        return $response;
    }

    public function blockaffectationsAction()
    {
        $eleveId = $this->params('eleveId');
        $trajet = $this->params('trajet');
        $resultset = $this->db_manager->get('Sbm/Db/Query/AffectationsServicesStations')->getAffectations($eleveId, $trajet);
        $structure = null;
        if ($resultset->count()) {
            $structure = array();
            foreach ($resultset as $affectation) {
                $structure[$affectation['jours']][$affectation['sens']][$affectation['correspondance']] = array(
                    'service1Id' => $affectation['service1Id'],
                    'station1Id' => $affectation['station1Id'],
                    'station1' => $affectation['station1'],
                    'service2Id' => $affectation['service2Id'],
                    'station2Id' => $affectation['station2Id'],
                    'station2' => $affectation['station2']
                );
            }
        }
        return new ViewModel(array(
            'identite' => $this->params('identite'),
            'structure' => $structure,
            'trajet' => $trajet
        ));
    }

    public function enableaccordbuttonAction()
    {
        $eleveId = $this->params('eleveId');
        $trajet = $this->params('trajet');
        $resultset = $this->db_manager->get('Sbm/Db/Query/AffectationsServicesStations')->getAffectations($eleveId, $trajet);
        $enable = $resultset->count() ? 0 : 1;
        return $this->getResponse()->setContent(Json::encode(array(
            'enable' => $enable,
            'success' => 1
        )));
    }

    public function getstationsforselectAction()
    {
        $serviceId = $this->params('serviceId');
        $queryStations = $this->db_manager->get('Sbm\Db\Select\Stations');
        $stations = $queryStations->surcircuit($serviceId, Session::get('millesime'));
        return $this->getResponse()->setContent(Json::encode(array(
            'data' => $stations,
            'success' => 1
        )));
    }

    /**
     * ajax - cocher la case accordR1 des élèves
     *
     * @method GET
     * @return dataType json
     */
    public function checkaccordR1Action()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setAccord(Session::get('millesime'), $eleveId, 'R1', 1);
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
     * ajax - décocher la case accordR1 des élèves
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckaccordR1Action()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setAccord(Session::get('millesime'), $eleveId, 'R1', 0);
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
     * ajax - cocher la case accordR2 des élèves
     *
     * @method GET
     * @return dataType json
     */
    public function checkaccordR2Action()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setAccord(Session::get('millesime'), $eleveId, 'R2', 1);
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
     * ajax - décocher la case accordR2 des élèves
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckaccordR2Action()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setAccord(Session::get('millesime'), $eleveId, 'R2', 0);
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

    public function decrementeduplicataAction()
    {
        try {
            $eleveId = $this->params('eleveId');
            $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
            $odata = $tScolarites->getRecord(array(
                'millesime' => Session::get('millesime'),
                'eleveId' => $eleveId
            ));
            if ($odata->duplicata > 0) {
                $odata->duplicata --;
            }
            $tScolarites->saveRecord($odata);
            return $this->getResponse()->setContent(Json::encode(array(
                'duplicata' => $odata->duplicata,
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