<?php
/**
 * Actions destinées aux réponses à des demandes ajax pour les élèves et les responsables
 *
 * Le layout est désactivé dans ce module
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package SbmAjax/Controller
 * @filesource EleveController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 août 2021
 * @version 2021-2.6.3
 */
namespace SbmAjax\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCartographie\GoogleMaps;
use SbmCartographie\Model\Point;
use SbmCommun\Model\Strategy\Semaine;
use SbmCommun\Model\Traits\ServiceTrait;
use Zend\Json\Json;
use Zend\Log\Logger;
use Zend\View\Model\ViewModel;

/**
 *
 * Attention ! Cette classe dérive d'une classe AbstractActionController spéciale pour ce
 * module
 *
 *
 * @property \SbmCommun\Model\Db\Service\DbManager $db_manager
 * @property \SbmCommun\Model\Service\FormManager $form_manager
 * @property \SbmCartographie\Model\Service\CartographieManager $cartographie_manager
 * @property array $img
 *
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 *
 */
class EleveController extends AbstractActionController
{
    use ServiceTrait, \SbmCommun\Model\Traits\DebugTrait;

    const ROUTE = 'sbmajaxeleve';

    public function indexAction()
    {
        return new ViewModel([
            'args' => $this->params('args', null)
        ]);
    }

    /**
     * ajax - cocher la case sélection des responsables
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkselectionresponsableAction()
    {
        try {
            $responsableId = $this->params('responsableId');
            $this->db_manager->get('Sbm\Db\Table\Responsables')->setSelection(
                $responsableId, 1);
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
     * ajax - décocher la case sélection des responsables
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckselectionresponsableAction()
    {
        try {
            $responsableId = $this->params('responsableId');
            $this->db_manager->get('Sbm\Db\Table\Responsables')->setSelection(
                $responsableId, 0);
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
     * ajax - cocher la case sélection des élèves
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkselectioneleveAction()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Eleves')->setSelection($eleveId, 1);
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
     * ajax - décocher la case sélection des élèves
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckselectioneleveAction()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Eleves')->setSelection($eleveId, 0);
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
     * ajax - renvoie une structure permettant d'adapter le Select de classeId en fonction
     * du niveau de l'établissement dont l'identifiant est etablissementId passé par GET
     * method GET Cette méthode est aussi dans ParentController
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
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

    /**
     * ajax - change responsable Renvoie les données d'un responsable dont la référence
     * est passée par GET
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function getresponsableAction()
    {
        try {
            $responsableId = $this->params('responsableId');
            $responsable = $this->db_manager->get('Sbm\Db\Vue\Responsables')->getRecord(
                $responsableId);
            foreach ([
                'telephoneF',
                'telephoneP',
                'telephoneT'
            ] as $telephone) {
                if ($responsable->{$telephone}) {
                    $responsable->{$telephone} = implode(' ',
                        str_split($responsable->{$telephone}, 2));
                }
            }
            return $this->getResponse()->setContent(
                Json::encode(
                    array_merge($responsable->getArrayCopy(), [
                        'success' => 1
                    ])));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    public function suppraffectationsAction()
    {
        $millesime = Session::get('millesime');
        $eleveId = $this->params('eleveId', 0);
        $responsableId = $this->params('responsableId', 0);
        try {
            $tAffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
            $affectedRows = $tAffectations->deleteResponsableId($millesime, $eleveId,
                $responsableId);
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $affectedRows,
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
     * Initialise et renvoie le formulaire d'affectation (méthode utilisée dans les
     * méthodes publiques formaffectationAction() et formaffectationvalidateAction()
     *
     * @return \SbmGestion\Form\AffectationDecision
     */
    private function getFormAffectationDecision(string $etablissementId, $trajet,
        bool $tout = false)
    {
        $values_options1 = $this->db_manager->get('Sbm\Db\Select\Stations')->ouvertes();
        if ($tout) {
            $values_options2 = $this->db_manager->get('Sbm\Db\Select\Services')->tout();
        } else {
            $values_options2 = $this->db_manager->get('Sbm\Db\Select\Services')->desservent(
                $etablissementId);
        }
        $form = new \SbmGestion\Form\AffectationDecision($trajet, 2);
        $form->remove('back');
        $form->setAttribute('action',
            $this->url()
                ->fromRoute(self::ROUTE, [
                'action' => 'formaffectationvalidate'
            ]));
        $form->setValueOptions('station1Id', $values_options1)
            ->setValueOptions('station2Id', $values_options1)
            ->setValueOptions('service1Id', $values_options2);
        // modif 18/07/2020 ->setValueOptions('service2Id', $values_options2);
        return $form;
    }

    private function getJoursOptions(array $aJoursPossibles = [])
    {
        $oSemaine = new Semaine();
        $aSemaine = [];
        foreach ($oSemaine::getJours() as $key => $value) {
            $option = [
                'label' => $value,
                'value' => $key
            ];
            if (! in_array($key, $aJoursPossibles)) {
                $option['attributes'] = [
                    'onclick' => 'return false;'
                ];
            }
            $aSemaine[] = $option;
        }
        return $aSemaine;
    }

    /**
     * Renvoie le code html d'un formulaire $trajet est le numéro du responsable (1 ou 2)
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function formaffectationAction()
    {
        $etablissementId = $this->params('etablissementId', '');
        $trajet = $this->params('trajet', 1);
        $station1Id = $this->params('station1Id', null);
        $station2Id = $this->params('station2Id', null);
        $form = $this->getFormAffectationDecision($etablissementId, $trajet,
            $this->params('op', '') == 'delete');
        try {
            $tCircuits = $this->db_manager->get('Sbm\Db\Table\Circuits');
            $circuit = $tCircuits->getRecord(
                [
                    'millesime' => Session::get('millesime'),
                    'ligneId' => $this->params('ligne1Id', 'xxxx'),
                    'sens' => $this->params('sensligne1'),
                    'moment' => $this->params('moment', '1'),
                    'ordre' => $this->params('ordreligne1'),
                    'stationId' => $station1Id ?: - 1
                ]);
            $aJours = $circuit->semaine;
            $form->setValueOptions('jours', $this->getJoursOptions($aJours));
        } catch (\Exception $e) {
            $aJours = 31; // sera transformé dans form->setData()
            $form->setValueOptions('jours', $this->getJoursOptions());
        }
        $aData = [
            'millesime' => Session::get('millesime'),
            'etablissementId' => $etablissementId,
            'eleveId' => $this->params('eleveId', 0),
            'trajet' => $trajet,
            'jours' => $this->params('jours', $aJours),
            'moment' => $this->params('moment', '1'),
            'correspondance' => $this->params('correspondance', 1),
            'responsableId' => $this->params('responsableId', 0),
            'station1Id' => $station1Id,
            'station2Id' => $station2Id,
            'ligne1Id' => $this->params('ligne1Id', null),
            'sensligne1' => $this->params('sensligne1'),
            'ordreligne1' => $this->params('ordreligne1'),
            'ligne2Id' => $this->params('ligne2Id', null),
            'sensligne2' => $this->params('sensligne2'),
            'ordreligne2' => $this->params('ordreligne2'),
            'op' => $this->params('op', null)
        ];
        return new ViewModel(
            [
                'trajet' => $trajet,
                'station1Id' => $station1Id,
                'station2Id' => $station2Id,
                'form' => $form->setData($aData),
                'is_xmlhttprequest' => 1
            ]);
    }

    /**
     * Traite le post du formulaire formaffectation
     *
     * @method POST
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function formaffectationvalidateAction()
    {
        $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/logs'), 'sbm_error.log');
        $request = $this->getRequest();
        $response = $this->getResponse();
        if ($request->isPost()) {
            if ($request->getPost('cancel') || $request->getPost('submit') == 'cancel') {
                $messages = 'Opération abandonnée.';
                $this->flashMessenger()->addInfoMessage($messages);
                $response->setContent(Json::encode([
                    'cr' => $messages,
                    'success' => 1
                ]));
            } else {
                $form = $this->getFormAffectationDecision(
                    $request->getPost('etablissementId'), $request->getPost('trajet'),
                    $request->getPost('op') == 'delete')
                    ->setValueOptions('jours', $this->getJoursOptions())
                    ->setData($request->getPost());
                if (! $form->isValid()) {
                    $errors = $form->getMessages();
                    $messages = '';
                    foreach ($errors as $key => $row) {
                        if (! empty($row) && $key != 'submit') {
                            foreach ($row as $rower) {
                                // save error(s) per-element that needed by Javascript
                                $messages .= $key . ' : ' . _($rower) . "\n";
                            }
                        }
                    }
                    $this->debugLog(
                        sprintf('%s (%d) : %s', __METHOD__, __LINE__, $messages));
                    $response->setContent(
                        Json::encode([
                            'cr' => $messages,
                            'success' => 0
                        ]));
                } else {
                    $tAffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
                    $oData = $tAffectations->getObjData();
                    $oData->exchangeArray($form->getData());
                    try {
                        switch ($form->getData()['op']) {
                            case 'add':
                                // insertRecord pour calculer le bon
                                // correspondance
                                $tAffectations->insertRecord($oData);
                                $messages = 'Nouvelle affectation enregistrée.';
                                $this->flashMessenger()->addSuccessMessage(
                                    'Nouvelle affectation enregistrée.');
                                break;
                            case 'edit':
                                $oAncien = clone $oData;
                                $oAncien->jours = $form->getData()['days'];
                                $tAffectations->updateRecord($oAncien, $oData);
                                $messages = 'Affectation modifiée.';
                                $this->flashMessenger()->addSuccessMessage(
                                    'Affectation modifiée.');
                                break;
                            case 'delete':
                                // ré-écrire deleteRecord pour mettre à jour les
                                // correspondances qui restent
                                $oData->jours = $form->getData()['days'];
                                $tAffectations->deleteRecord($oData);
                                $messages = 'Affectation supprimée.';
                                $this->flashMessenger()->addSuccessMessage(
                                    'Affectation supprimée.');
                                break;
                            default:
                                $messages = 'Demande incorrecte.';
                                $this->flashMessenger()->addWarningMessage(
                                    'Demande incorrecte.');
                                break;
                        }
                        $response->setContent(
                            Json::encode(
                                [
                                    'cr' => "$messages",
                                    'nb' => $tAffectations->count($oData->millesime,
                                        $oData->eleveId),
                                    'success' => 1
                                ]));
                    } catch (\Exception $e) {
                        $this->debugLog(
                            sprintf('%s (%d) : %s', __METHOD__, __LINE__, $e->getMessage()));
                        $this->flashMessenger()->addErrorMessage(
                            'Une erreur s\'est produite pendant le traitement de la demande.');
                        $response->setContent(
                            Json::encode([
                                'cr' => $e->getMessage(),
                                'success' => 0
                            ]));
                    }
                }
            }
        } else {
            $response->setContent(
                Json::encode([
                    'cr' => 'Pas de post !',
                    'success' => 0
                ]));
        }

        return $response;
    }

    private function getFormStationDepart()
    {
        $form = new \SbmGestion\Form\StationDepart();
        $form->setAttribute('action',
            $this->url()
                ->fromRoute(self::ROUTE,
                [
                    'action' => 'formchercheraffectationsvalidate'
                ]))
            ->setValueOptions('stationId',
            $this->db_manager->get('Sbm\Db\Select\Stations')
                ->ouvertes());
        return $form;
    }

    public function formchercheraffectationsAction()
    {
        $etablissementId = $this->params('etablissementId', '');
        $trajet = $this->params('trajet', 1);
        $millesime = Session::get('millesime');
        $eleveId = $this->params('eleveId', 0);
        $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $oscolarite = $tScolarites->getRecord(
            [
                'millesime' => $millesime,
                'eleveId' => $eleveId
            ]);
        $stationId = $oscolarite->{'stationIdR' . $trajet};
        $aData = [
            'millesime' => $millesime,
            'etablissementId' => $etablissementId,
            'eleveId' => $eleveId,
            'trajet' => $trajet,
            'jours' => $this->params('jours', 31), // 31:LMMJV
            'responsableId' => $this->params('responsableId', 0),
            'regimeId' => $oscolarite->regimeId,
            'op' => $this->params('op', null),
            'stationId' => $stationId
        ];
        return new ViewModel(
            [
                'trajet' => $trajet,
                'stationId' => $stationId,
                'form' => $this->getFormStationDepart()->setData($aData),
                'is_xmlhttprequest' => 1
            ]);
    }

    public function formchercheraffectationsvalidateAction()
    {
        $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/logs'), 'sbm_error.log');
        $request = $this->getRequest();
        $response = $this->getResponse();
        if ($request->isPost()) {
            if (($request->getPost('cancel') || $request->getPost('submit') == 'cancel')) {
                $messages = 'Opération abandonnée.';
                $this->flashMessenger()->addInfoMessage($messages);
                $response->setContent(Json::encode([
                    'cr' => $messages,
                    'success' => 1
                ]));
            } else {
                $form = $this->getFormStationDepart()->setData($request->getPost());
                if (! $form->isValid()) {
                    $errors = $form->getMessages();
                    $messages = '';
                    foreach ($errors as $key => $row) {
                        if (! empty($row) && $key != 'submit') {
                            foreach ($row as $rower) {
                                // save error(s) per-element that needed by Javascript
                                $messages .= $key . ' : ' . _($rower) . "\n";
                            }
                        }
                    }
                    $this->debugLog(
                        sprintf('%s (%d) : %s', __METHOD__, __LINE__, $messages));
                    $response->setContent(
                        Json::encode([
                            'cr' => $messages,
                            'success' => 0
                        ]));
                } else {
                    try {
                        $data = $form->getData();
                        if ($data['raz'] == 'RAZ') {
                            $tAffectations = $this->db_manager->get(
                                'Sbm\Db\Table\Affectations');
                            $tAffectations->deleteTrajet(Session::get('millesime'),
                                $data['eleveId'], $data['trajet']);
                        }
                        switch ($data['op']) {
                            case 'auto':
                                $tScolarites = $this->db_manager->get(
                                    'Sbm\Db\Table\Scolarites');
                                $oScolarite = $tScolarites->getRecord(
                                    [
                                        'millesime' => Session::get('millesime'),
                                        'eleveId' => $data['eleveId']
                                    ]);
                                $oScolarite->{'stationIdR' . $data['trajet']} = $data['stationId'];
                                $tScolarites->saveRecord($oScolarite);
                                $this->db_manager->get('Sbm\ChercheItineraires')
                                    ->setEtablissementId($data['etablissementId'])
                                    ->setStationId($data['stationId'])
                                    ->setEleveId($oScolarite->eleveId)
                                    ->setJours($data['jours'])
                                    ->setTrajet($data['trajet'])
                                    ->setResponsableId($data['responsableId'])
                                    ->setRegimeId($data['regimeId'])
                                    ->run();
                                $messages = 'Nouvelles affectations enregistrées.';
                                $this->flashMessenger()->addSuccessMessage($messages);
                                $tAffectations = $this->db_manager->get(
                                    'Sbm\Db\Table\Affectations');
                                $response->setContent(
                                    Json::encode(
                                        [
                                            'cr' => "$messages",
                                            'nb' => $tAffectations->count(
                                                $oScolarite->millesime,
                                                $oScolarite->eleveId,
                                                $data['responsableId']),
                                            'success' => 1
                                        ]));
                                break;
                            default:
                                $messages = 'Demande incorrecte.';
                                $this->flashMessenger()->addWarningMessage($messages);
                                break;
                        }
                    } catch (\Exception $e) {
                        $this->debugLog(
                            sprintf('%s (%d) : %s', __METHOD__, __LINE__, $e->getMessage()));
                        $this->flashMessenger()->addErrorMessage(
                            'Une erreur s\'est produite pendant le traitement de la demande.');
                        $response->setContent(
                            Json::encode([
                                'cr' => $e->getMessage(),
                                'success' => 0
                            ]));
                    }
                }
            }
        } else {
            $response->setContent(
                Json::encode([
                    'cr' => 'Pas de post !',
                    'success' => 0
                ]));
        }

        return $response;
    }

    private function getFormPriseEnChargePaiement()
    {
        $form = new \SbmGestion\Form\Eleve\PriseEnChargePaiement();

        $form->setAttribute('action',
            $this->url()
                ->fromRoute(self::ROUTE, [
                'action' => 'formpaiementvalidate'
            ]));
        $form->setValueOptions('organismeId',
            $this->db_manager->get('Sbm\Db\Select\Organismes'));
        return $form;
    }

    public function formpaiementAction()
    {
        $response = $this->getResponse();
        $eleveId = $this->params('eleveId', 0);
        if ($eleveId) {
            $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
            $oData = $tScolarites->getRecord(
                [
                    'millesime' => Session::get('millesime'),
                    'eleveId' => $eleveId
                ]);
            $aData = [
                'eleveId' => $eleveId,
                'gratuit' => $oData->gratuit,
                'organismeId' => $oData->organismeId
            ];
            return new ViewModel(
                [
                    'form' => $this->getFormPriseEnChargePaiement()->setData($aData),
                    'is_xmlhttprequest' => 1
                ]);
        } else {
            $response->setContent(
                Json::encode([
                    'cr' => 'Pas de référence élève !',
                    'success' => 0
                ]));
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
                $response->setContent(Json::encode([
                    'cr' => $messages,
                    'success' => 1
                ]));
            } else {
                $form = $this->getFormPriseEnChargePaiement()->setData(
                    $request->getPost());
                if (! $form->isValid()) {
                    $errors = $form->getMessages();
                    $messages = '';
                    foreach ($errors as $key => $row) {
                        if (! empty($row) && $key != 'submit') {
                            foreach ($row as $rower) {
                                // save error(s) per-element that needed by Javascript
                                $messages .= $key . ' : ' . _($rower) . "\n";
                            }
                        }
                    }
                    $response->setContent(
                        Json::encode([
                            'cr' => $messages,
                            'success' => 0
                        ]));
                } else {
                    $data = $form->getData();
                    $eleveId = $data['eleveId'];
                    $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
                    try {
                        $oData = $tScolarites->getRecord(
                            [
                                'millesime' => Session::get('millesime'),
                                'eleveId' => $eleveId
                            ]);
                        $oData->gratuit = $data['gratuit'];
                        if ($data['gratuit'] == 2) {
                            $oData->organismeId = $data['organismeId'];
                        } else {
                            $oData->organismeId = 0;
                        }
                        $tScolarites->updateRecord($oData);
                        $messages = 'La prise en charge du paiement a été modifiée.';
                        $this->flashMessenger()->addSuccessMessage($messages);
                        $response->setContent(
                            Json::encode([
                                'cr' => "$messages",
                                'success' => 1
                            ]));
                    } catch (\Exception $e) {
                        $this->flashMessenger()->addErrorMessage(
                            'Une erreur s\'est produite pendant le traitement de la demande.');
                        $response->setContent(
                            Json::encode([
                                'cr' => $e->getMessage(),
                                'success' => 0
                            ]));
                    }
                }
            }
        } else {
            $response->setContent(
                Json::encode([
                    'cr' => 'Pas de post !',
                    'success' => 0
                ]));
        }

        return $response;
    }

    /**
     * Renvoie un cr success = 1 ou 0
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function demandetraiteeAction()
    {
        try {
            $eleveId = $this->params('eleveId');
            $trajet = $this->params('trajet');
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setDemandeTraitee(
                Session::get('millesime'), $eleveId, $trajet);
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
     * Renvoie la distance d'un domicile à un établissement L'argument passé en GET dans
     * args contient une chaine de la forme responsableId:valeur/etablissementId:valeur
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function donnedistanceAction()
    {
        $response = $this->getResponse();
        try {
            $etablissementId = $this->params('etablissementId', false);
            $responsableId = $this->params('responsableId', false);
            $distance = 0;
            if ($etablissementId && $responsableId) {
                $responsable = $this->db_manager->get('Sbm\Db\Table\Responsables')->getRecord(
                    $responsableId);
                $origine = new Point($responsable->x, $responsable->y);
                $etablissement = $this->db_manager->get('Sbm\Db\Table\Etablissements')->getRecord(
                    $etablissementId);
                $destination = new Point($etablissement->x, $etablissement->y);
                $oOrigineDestination = $this->cartographie_manager->get(
                    GoogleMaps\DistanceMatrix::class);
                $distance = $oOrigineDestination->calculDistance($origine, $destination);
                if ($distance) {
                    $distance = round($distance / 1000, 1);
                }
            }
            $response->setContent(
                Json::encode([
                    'distance' => $distance,
                    'success' => 1
                ]));
        } catch (GoogleMaps\Exception\ExceptionNoAnswer $e) {
            $response->setContent(Json::encode([
                'distance' => 99,
                'success' => 1
            ]));
        } catch (\Exception $e) {
            $response->setContent(
                Json::encode(
                    [
                        'cr' => sprintf("%s\n%s", $e->getMessage(), $e->getTraceAsString()),
                        'success' => 0
                    ]));
        }

        return $response;
    }

    /**
     * Recrée la même structure que celle générée dans
     * SbmGestion/view/sbm-gestion/eleve/eleve-edit.phtml
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function blockaffectationsAction()
    {
        $query = $this->db_manager->get('Sbm/Db/Query/AffectationsServicesStations');
        $eleveId = $this->params('eleveId');
        $trajet = $this->params('trajet');
        return new ViewModel(
            [
                'identite' => $this->params('identite'),
                'structure' => [
                    'annee_courante' => \SbmCommun\Model\View\StructureAffectations::get(
                        $query->getAffectations($eleveId, $trajet, false)),
                    'annee_precedente' => \SbmCommun\Model\View\StructureAffectations::get(
                        $query->getAffectations($eleveId, $trajet, true))
                ],
                'trajet' => $trajet
            ]);
    }

    public function enableaccordbuttonAction()
    {
        $eleveId = $this->params('eleveId');
        $trajet = $this->params('trajet');
        $resultset = $this->db_manager->get('Sbm/Db/Query/AffectationsServicesStations')->getAffectations(
            $eleveId, $trajet);
        $enable = $resultset->count() ? 0 : 1;
        return $this->getResponse()->setContent(
            Json::encode([
                'enable' => $enable,
                'success' => 1
            ]));
    }

    public function getstationsforselectAction()
    {
        $serviceId = $this->params('serviceId');
        $arrayServiceId = $this->decodeServiceId($serviceId);
        $queryStations = $this->db_manager->get('Sbm\Db\Select\Stations');
        $stations = $queryStations->surcircuit(Session::get('millesime'),
            $arrayServiceId['ligneId'], $arrayServiceId['sens'], $arrayServiceId['moment'],
            $arrayServiceId['ordre']);
        return $this->getResponse()->setContent(
            Json::encode(
                [
                    'data' => array_flip($stations), // échange key/value pour conserver
                                                      // le tri
                    'success' => 1
                ]));
    }

    public function getpossiblejoursAction()
    {
        $serviceId = $this->params('serviceId');
        $arrayServiceId = $this->decodeServiceId($serviceId);
        try {
            $tServices = $this->db_manager->get('Sbm\Db\Table\Services');
            $service = $tServices->getRecord(
                [
                    'millesime' => Session::get('millesime'),
                    'ligneId' => $arrayServiceId['ligneId'],
                    'sens' => $arrayServiceId['sens'],
                    'moment' => $arrayServiceId['moment'],
                    'ordre' => $arrayServiceId['ordre']
                ]);
            $aJours = $service->semaine;
            $possibleJours = $this->getJoursOptions($aJours);
        } catch (\Exception $e) {
            $aJours = 31;
            $possibleJours = $this->getJoursOptions();
        }
        return $this->getResponse()->setContent(
            Json::encode([
                'data' => $possibleJours,
                'success' => 1
            ]));
    }

    /**
     * ajax - cocher la case accordR1 des élèves
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkaccordR1Action()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setAccord(
                Session::get('millesime'), $eleveId, 'R1', 1);
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
     * ajax - décocher la case accordR1 des élèves
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckaccordR1Action()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setAccord(
                Session::get('millesime'), $eleveId, 'R1', 0);
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
     * ajax - cocher la case accordR2 des élèves
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkaccordR2Action()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setAccord(
                Session::get('millesime'), $eleveId, 'R2', 1);
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
     * ajax - décocher la case accordR2 des élèves
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckaccordR2Action()
    {
        try {
            $eleveId = $this->params('eleveId');
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setAccord(
                Session::get('millesime'), $eleveId, 'R2', 0);
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
     * ajax - décrémenter duplicata dans la table scolarites
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function decrementeduplicataAction()
    {
        try {
            $eleveId = $this->params('eleveId');
            $trajet = $this->params('trajet');
            $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
            $odata = $tScolarites->getRecord(
                [
                    'millesime' => Session::get('millesime'),
                    'eleveId' => $eleveId
                ]);
            if ($odata->{'duplicataR' . $trajet} > 0) {
                $odata->{'duplicataR' . $trajet} --;
            }
            $tScolarites->saveRecord($odata);
            return $this->getResponse()->setContent(
                Json::encode(
                    [
                        'duplicataR' . $trajet => $odata->{'duplicataR' . $trajet},
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
     * ajax - incrémenter duplicata dans la table scolarites
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function incrementeduplicataAction()
    {
        try {
            $eleveId = $this->params('eleveId');
            $trajet = $this->params('trajet');
            $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
            $odata = $tScolarites->getRecord(
                [
                    'millesime' => Session::get('millesime'),
                    'eleveId' => $eleveId
                ]);
            $odata->{'duplicataR' . $trajet} ++;
            $tScolarites->saveRecord($odata);
            return $this->getResponse()->setContent(
                Json::encode(
                    [
                        'duplicataR' . $trajet => $odata->{'duplicataR' . $trajet},
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
     * Reçoie en POST les données du formulaire
     * \SbmCommun\Model\Photo\Photo::getForm()<ul> <li>eleveId (post)</li> <li>filephoto
     * (files)</li></ul> Vérifie si elles sont valide et les enregistre dans la table
     * `elevesphotos` Renvoie un compte rendu :<ul> <li>success = 1 et src = la chaine src
     * à placer dans la balise `img`</li> <li>success = 10x et cr = la chaine à afficher
     * en cas d'erreur</li></ul>
     *
     * @method POST
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function savephotoAction()
    {
        $request = $this->getRequest();
        if (! $request->isPost()) {
            // ce n'est pas un post : on renvoie une erreur
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => 'Action incorrecte.',
                    'success' => 102
                ]));
        }
        $post = array_merge_recursive($request->getPost()->toArray(),
            $request->getFiles()->toArray());
        $ophoto = new \SbmCommun\Model\Photo\Photo();
        $ophoto->setFileLog($this->img['log']['path_filelog'],
            $this->img['log']['filename']);
        $form = $ophoto->getFormWithInputFilter($this->img['path']['tmpuploads'])->prepare();
        $form->setData($post);
        if ($form->isValid()) {
            $data = $form->getData();
            $source = $data['filephoto']['tmp_name'];
            try {
                $blob = $ophoto->getImageJpegAsString($source);
                unlink($source);
            } catch (\Exception $e) {
                // problème de fichier, de format de fichier ou d'image dont le format
                // n'est pastraité
                $ophoto->getLogger()->log(Logger::ERR, $e->getMessage());
                $ophoto->getLogger()->log(Logger::DEBUG, $e->getTraceAsString());
                $msg = explode('.', $e->getMessage());
                return $this->getResponse()->setContent(
                    Json::encode([
                        'cr' => $msg[0],
                        'success' => 101
                    ]));
            }
        } else {
            return $this->getResponse()->setContent(
                Json::encode(
                    [
                        'cr' => implode(', ', $ophoto->getMessagesFilePhotoElement()),
                        'success' => 100
                    ]));
        }
        // base de données
        $tPhotos = $this->db_manager->get('Sbm\Db\Table\ElevesPhotos');
        $odata = $tPhotos->getObjData();
        $odata->exchangeArray(
            [
                'eleveId' => $data['eleveId'],
                'photo' => addslashes($blob)
            ]);
        $tPhotos->saveRecord($odata);
        return $this->getResponse()->setContent(
            Json::encode([
                'src' => $ophoto->img_src($blob),
                'success' => 1
            ]));
    }

    /**
     * Reçoie en POST le paramètre eleveId et supprime la photo dans la table
     * ElevesPhotos.
     * Renvoie un compte rendu : <ul> <li>success = 1 et src = sans photo
     * gif</li> <li>success = 20x et cr = message d'erreur à afficher en cas
     * d'erreur</li></ul>
     *
     * @method POST
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function supprphotoAction()
    {
        if (! $this->getRequest()->isPost()) {
            // ce n'est pas un post : on renvoie une erreur
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => 'Action incorrecte.',
                    'success' => 202
                ]));
        }
        if ($eleveId = $this->getRequest()->getPost('eleveId', null)) {
            $tPhotos = $this->db_manager->get('Sbm\Db\Table\ElevesPhotos');
            $success = $tPhotos->deleteRecord($eleveId);
            if ($success) {
                $ophoto = new \SbmCommun\Model\Photo\Photo();
                return $this->getResponse()->setContent(
                    Json::encode(
                        [
                            'src' => $ophoto->img_src($ophoto->getSansPhotoGifAsString(),
                                'gif'),
                            'success' => 1
                        ]));
            } else {
                return $this->getResponse()->setContent(
                    Json::encode(
                        [
                            'cr' => 'Impossible de supprimer cette photo',
                            'success' => 201
                        ]));
            }
            ;
        }
    }

    public function quartgauchephotoAction()
    {
        if (! $this->getRequest()->isPost()) {
            // ce n'est pas un post : on renvoie une erreur
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => 'Action incorrecte.',
                    'success' => 202
                ]));
        }
        if ($eleveId = $this->getRequest()->getPost('eleveId', null)) {
            $ophoto = new \SbmCommun\Model\Photo\Photo();
            $tPhotos = $this->db_manager->get('Sbm\Db\Table\ElevesPhotos');
            $odata = $tPhotos->getRecord($eleveId);
            $blob = $ophoto->rotate(stripslashes($odata->photo), 90);
            $odata->photo = addslashes($blob);
            $tPhotos->saveRecord($odata);
            return $this->getResponse()->setContent(
                Json::encode([
                    'src' => $ophoto->img_src($blob),
                    'success' => 1
                ]));
        }
    }

    public function quartdroitephotoAction()
    {
        if (! $this->getRequest()->isPost()) {
            // ce n'est pas un post : on renvoie une erreur
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => 'Action incorrecte.',
                    'success' => 202
                ]));
        }
        if ($eleveId = $this->getRequest()->getPost('eleveId', null)) {
            $ophoto = new \SbmCommun\Model\Photo\Photo();
            $tPhotos = $this->db_manager->get('Sbm\Db\Table\ElevesPhotos');
            $odata = $tPhotos->getRecord($eleveId);
            $blob = $ophoto->rotate(stripslashes($odata->photo), - 90);
            $odata->photo = addslashes($blob);
            $tPhotos->saveRecord($odata);
            return $this->getResponse()->setContent(
                Json::encode([
                    'src' => $ophoto->img_src($blob),
                    'success' => 1
                ]));
        }
    }

    public function retournephotoAction()
    {
        if (! $this->getRequest()->isPost()) {
            // ce n'est pas un post : on renvoie une erreur
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => 'Action incorrecte.',
                    'success' => 202
                ]));
        }
        if ($eleveId = $this->getRequest()->getPost('eleveId', null)) {
            $ophoto = new \SbmCommun\Model\Photo\Photo();
            $tPhotos = $this->db_manager->get('Sbm\Db\Table\ElevesPhotos');
            $odata = $tPhotos->getRecord($eleveId);
            $blob = $ophoto->rotate(stripslashes($odata->photo), 180);
            $odata->photo = addslashes($blob);
            $tPhotos->saveRecord($odata);
            return $this->getResponse()->setContent(
                Json::encode([
                    'src' => $ophoto->img_src($blob),
                    'success' => 1
                ]));
        }
    }

    public function inviteformonglet1Action()
    {
        $form = $this->form_manager->get(\SbmCommun\Form\Invite::class);
        $form->setValueOptions('eleveId',
            $this->db_manager->get('Sbm\Db\Select\Eleves')
                ->inscrits())
            ->setValueOptions('communeId',
            $this->db_manager->get('Sbm\Db\Select\Communes')
                ->visibles())
            ->setValueOptions('stationId',
            $this->db_manager->get('Sbm\Db\Select\Stations')
                ->ouvertes())
            ->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis());
        try {
            $inviteId = (int) $this->params('inviteId');
            if ($inviteId > 0) {
                $form->setEtat(1)->setData(
                    $this->db_manager->get('Sbm\Db\Table\Invites')
                        ->getRecord($inviteId)
                        ->getArrayCopy());
            }
            return new ViewModel([
                'form' => $form
            ]);
        } catch (\Exception $e) {
            $msg = '<pre>';
            $msg = __METHOD__ . "\n";
            $msg .= $e->getMessage() . "\n";
            $msg .= $e->getTraceAsString();
            $msg .= '</pre>';
            return $msg;
        }
    }

    public function inviteformonglet2Action()
    {
        $form = $this->form_manager->get(\SbmCommun\Form\Invite::class);
        $form->setValueOptions('eleveId',
            $this->db_manager->get('Sbm\Db\Select\Eleves')
                ->inscrits());
        try {
            $inviteId = (int) $this->params('inviteId');
            if ($inviteId > 0) {
                $form->setEtat(2)->setData(
                    $this->db_manager->get('Sbm\Db\Table\Invites')
                        ->getRecord($inviteId)
                        ->getArrayCopy());
            }
            return new ViewModel([
                'form' => $form
            ]);
        } catch (\Exception $e) {
            $msg = '<pre>';
            $msg = __METHOD__ . "\n";
            $msg .= $e->getMessage() . "\n";
            $msg .= $e->getTraceAsString();
            $msg .= '</pre>';
            return $msg;
        }
    }

    public function inviteformonglet3Action()
    {
        $form = $this->form_manager->get(\SbmCommun\Form\Invite::class);
        $form->setValueOptions('responsableId',
            $this->db_manager->get('Sbm\Db\Select\Responsables'))
            ->setValueOptions('stationId',
            $this->db_manager->get('Sbm\Db\Select\Stations')
                ->ouvertes())
            ->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis());
        try {
            $inviteId = (int) $this->params('inviteId');
            if ($inviteId > 0) {
                $form->setEtat(3)->setData(
                    $this->db_manager->get('Sbm\Db\Table\Invites')
                        ->getRecord($inviteId)
                        ->getArrayCopy());
            }
            return new ViewModel([
                'form' => $form
            ]);
            die(var_dump($view));
        } catch (\Exception $e) {
            $msg = '<pre>';
            $msg = __METHOD__ . "\n";
            $msg .= $e->getMessage() . "\n";
            $msg .= $e->getTraceAsString();
            $msg .= '</pre>';
            return $msg;
        }
    }

    public function inviteformonglet4Action()
    {
        $form = $this->form_manager->get(\SbmCommun\Form\Invite::class);
        $form->setValueOptions('organismeId',
            $this->db_manager->get('Sbm\Db\Select\Organismes'))
            ->setValueOptions('stationId',
            $this->db_manager->get('Sbm\Db\Select\Stations')
                ->ouvertes())
            ->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis());
        try {
            $inviteId = (int) $this->params('inviteId');
            if ($inviteId > 0) {
                $form->setEtat(4)->setData(
                    $this->db_manager->get('Sbm\Db\Table\Invites')
                        ->getRecord($inviteId)
                        ->getArrayCopy());
            }
            return new ViewModel([
                'form' => $form
            ]);
            die(var_dump($view));
        } catch (\Exception $e) {
            $msg = '<pre>';
            $msg = __METHOD__ . "\n";
            $msg .= $e->getMessage() . "\n";
            $msg .= $e->getTraceAsString();
            $msg .= '</pre>';
            return $msg;
        }
    }

    public function inviteformonglet5Action()
    {
        $form = $this->form_manager->get(\SbmCommun\Form\Invite::class);
        $form->setValueOptions('communeId',
            $this->db_manager->get('Sbm\Db\Select\Communes')
                ->visibles())
            ->setValueOptions('stationId',
            $this->db_manager->get('Sbm\Db\Select\Stations')
                ->ouvertes())
            ->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis());
        try {
            $inviteId = (int) $this->params('inviteId');
            if ($inviteId > 0) {
                $form->setEtat(5)->setData(
                    $this->db_manager->get('Sbm\Db\Table\Invites')
                        ->getRecord($inviteId)
                        ->getArrayCopy());
            }
            return new ViewModel([
                'form' => $form
            ]);
        } catch (\Exception $e) {
            $msg = '<pre>';
            $msg = __METHOD__ . "\n";
            $msg .= $e->getMessage() . "\n";
            $msg .= $e->getTraceAsString();
            $msg .= '</pre>';
            return $msg;
        }
    }

    public function getEtablissementsValueOptions()
    {
    }

    public function getStationsValueOptions()
    {
        $serviceId = $this->params('serviceId');
        $arrayServiceId = $this->decodeServiceId($serviceId);
        $queryStations = $this->db_manager->get('Sbm\Db\Select\Stations');
        $stations = $queryStations->surcircuit(Session::get('millesime'),
            $arrayServiceId['ligneId'], $arrayServiceId['sens'], $arrayServiceId['moment'],
            $arrayServiceId['ordre']);
        return $this->getResponse()->setContent(
            Json::encode(
                [
                    'data' => array_flip($stations), // échange key/value pour conserver
                                                      // le tri
                    'success' => 1
                ]));
    }

    public function getLignesMomentValuesOptions()
    {
    }
}